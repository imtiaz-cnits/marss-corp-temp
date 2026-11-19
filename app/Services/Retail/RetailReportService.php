<?php

namespace App\Services\Retail;

use App\Models\Retail\RetailCustomer;
use App\Models\Retail\RetailCustomerPaymentDetail;
use App\Models\Retail\RetailExpense;
use App\Models\Retail\RetailInvest;
use App\Models\Retail\RetailOpeningBalance;
use App\Models\Retail\RetailOrder;
use App\Models\Retail\RetailOrderPaymentDetail;
use App\Models\Retail\RetailProduct;
use App\Models\Retail\RetailPurchasePaymentDetail;
use App\Models\Retail\RetailSupplier;
use App\Models\Retail\RetailSupplierDueCollection;
use App\Support\DateFilter;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Isolated Retail Analytics, Dashboard & Financial Reports Service.
 * Fixes FIN-01, FIN-02, FIN-05, FIN-06, FIN-07 and eliminates N+1 dashboard trend queries.
 */
class RetailReportService
{
    public function __construct(
        private readonly RetailStockService $stockService,
    ) {}

    /**
     * Full Retail Dashboard payload with single-query grouped trends and zero double-counting.
     *
     * @return array<string, mixed>
     */
    public function dashboardMetrics(string $filterType = 'today', ?string $startDate = null, ?string $endDate = null, bool $includeProfit = true): array
    {
        $range = DateFilter::resolveRange($filterType, $startDate, $endDate);
        $start = $range['start'];
        $end = $range['end'];

        $banner = $this->periodKpis($start, $end, $range['filterType'] === 'all_time', $includeProfit);

        $monthStart = Carbon::now()->startOfMonth()->toDateString();
        $today = Carbon::today()->toDateString();
        $monthly = $this->periodKpis($monthStart, $today, false, $includeProfit);

        $customerDueLifetime = Money::add(
            DB::table('customers')->selectRaw('COALESCE(SUM(CAST(COALESCE(previous_due_amount,0) AS DECIMAL(15,2))), 0) as s')->value('s'),
            DB::table('orders')->selectRaw('COALESCE(SUM(CAST(COALESCE(due_amount,0) AS DECIMAL(15,2))), 0) as s')->value('s'),
        );
        $supplierPayableLifetime = Money::add(
            DB::table('suppliers')->selectRaw('COALESCE(SUM(CAST(COALESCE(purchase_payable_amount,0) AS DECIMAL(15,2))), 0) as s')->value('s'),
            DB::table('purchases')->selectRaw('COALESCE(SUM(CAST(COALESCE(due_amount,0) AS DECIMAL(15,2))), 0) as s')->value('s'),
        );

        $stock = $this->stockService->summaryMetrics();
        $lowStockCount = (int) DB::table('products')->whereRaw('CAST(COALESCE(quantity,0) AS SIGNED) < 10')->count();

        $trend = $this->trendSeries($range['filterType'], $start, $end, $includeProfit);

        $lowStockProducts = RetailProduct::with(['unit', 'brand'])
            ->whereRaw('CAST(COALESCE(quantity,0) AS SIGNED) < 10')
            ->orderByRaw('CAST(COALESCE(quantity,0) AS SIGNED) ASC')
            ->limit(10)
            ->get();

        $recentSales = RetailOrder::with('customer')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return [
            'filter' => $range,
            'banner' => $banner,
            'monthly' => $monthly,
            'financial' => [
                'customer_due' => $customerDueLifetime,
                'supplier_payable' => $supplierPayableLifetime,
                'cost_stock_value' => $stock['cost_value'],
                'sell_stock_value' => $stock['sell_value'],
                'low_stock_count' => $lowStockCount,
            ],
            'entities' => [
                'products' => $stock['total_products'],
                'customers' => RetailCustomer::count(),
                'suppliers' => RetailSupplier::count(),
            ],
            'trend' => $trend,
            'low_stock_products' => $lowStockProducts,
            'recent_sales' => $recentSales,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function periodKpis(?string $start, ?string $end, bool $isAllTime, bool $includeProfit): array
    {
        $orderAgg = DateFilter::applyInvoiceDateFilter(DB::table('orders'), 'invoice_date', $start, $end)
            ->selectRaw('
                COUNT(*) as cnt,
                COALESCE(SUM(CAST(COALESCE(sub_total,0) AS DECIMAL(15,2))), 0) as sales_amount,
                COALESCE(SUM(CAST(COALESCE(discount_amount,0) AS DECIMAL(15,2))), 0) as discount_amount,
                COALESCE(SUM(CAST(COALESCE(due_amount,0) AS DECIMAL(15,2))), 0) as orders_due
            ')
            ->first();

        // Cash collection: ALL order_payment_details + ONLY previous-due customer_payment_details (fixes FIN-01 2x double-count)
        $orderPay = DateFilter::applyDateFilter(DB::table('order_payment_details'), 'due_collection_date', $start, $end)
            ->selectRaw('COALESCE(SUM(CAST(COALESCE(paid_amount,0) AS DECIMAL(15,2))), 0) as s')
            ->value('s');
        $prevCustPay = DateFilter::applyDateFilter(DB::table('customer_payment_details'), 'due_collection_date', $start, $end)
            ->whereRaw('CAST(COALESCE(previous_due_amount,0) AS DECIMAL(15,2)) > 0')
            ->selectRaw('COALESCE(SUM(CAST(COALESCE(paid_amount,0) AS DECIMAL(15,2))), 0) as s')
            ->value('s');
        $cashCollection = Money::add($orderPay, $prevCustPay);

        // Expenses
        $expense = Money::round(
            DateFilter::applyDateFilter(DB::table('expenses'), 'date', $start, $end)
                ->selectRaw('COALESCE(SUM(CAST(COALESCE(expense_amount,0) AS DECIMAL(15,2))), 0) as s')
                ->value('s')
        );

        // Purchases & Supplier Payments (fixes FIN-02 2x double-count)
        $purchaseAgg = DateFilter::applyDateFilter(DB::table('purchases'), 'date', $start, $end)
            ->selectRaw('
                COALESCE(SUM(CAST(COALESCE(grand_subtotal,0) AS DECIMAL(15,2))), 0) as purchase_amount,
                COALESCE(SUM(CAST(COALESCE(due_amount,0) AS DECIMAL(15,2))), 0) as purchase_due
            ')
            ->first();

        $purchasePay = DateFilter::applyDateFilter(DB::table('purchase_payment_details'), 'purchase_due_collection_date', $start, $end)
            ->selectRaw('COALESCE(SUM(CAST(COALESCE(paid_amount,0) AS DECIMAL(15,2))), 0) as s')
            ->value('s');
        $prevSuppPay = DateFilter::applyDateFilter(DB::table('supplier_due_collections'), 'due_collection_date', $start, $end)
            ->whereRaw('CAST(COALESCE(purchase_payable_amount,0) AS DECIMAL(15,2)) > 0')
            ->selectRaw('COALESCE(SUM(CAST(COALESCE(paid_amount,0) AS DECIMAL(15,2))), 0) as s')
            ->value('s');
        $supplierPayment = Money::add($purchasePay, $prevSuppPay);

        $returns = Money::round(
            DateFilter::applyDateFilter(DB::table('product_returns'), 'date', $start, $end)
                ->selectRaw('COALESCE(SUM(CAST(COALESCE(amount,0) AS DECIMAL(15,2))), 0) as s')
                ->value('s')
        );

        $grossProfit = '0.00';
        $netProfit = '0.00';
        if ($includeProfit) {
            $itemProfit = DateFilter::applyInvoiceDateFilter(
                DB::table('order_details')->join('orders', 'orders.id', '=', 'order_details.order_id'),
                'orders.invoice_date',
                $start,
                $end,
                'orders.created_at',
            )->selectRaw('
                COALESCE(SUM(
                    (CAST(COALESCE(order_details.selling_price,0) AS DECIMAL(15,2)) - CAST(COALESCE(order_details.price,0) AS DECIMAL(15,2)))
                    * GREATEST(0, CAST(COALESCE(order_details.quantity,0) AS DECIMAL(15,2)) - COALESCE(order_details.return_quantity,0))
                ), 0) as p
            ')->value('p');

            $grossProfit = Money::sub($itemProfit, $orderAgg->discount_amount ?? 0);
            $netProfit = Money::sub($grossProfit, $expense);
        }

        $customerDue = $isAllTime
            ? Money::add(
                DB::table('customers')->selectRaw('COALESCE(SUM(CAST(COALESCE(previous_due_amount,0) AS DECIMAL(15,2))), 0) as s')->value('s'),
                $orderAgg->orders_due ?? 0,
            )
            : Money::round($orderAgg->orders_due ?? 0);

        $supplierPayable = $isAllTime
            ? Money::add(
                DB::table('suppliers')->selectRaw('COALESCE(SUM(CAST(COALESCE(purchase_payable_amount,0) AS DECIMAL(15,2))), 0) as s')->value('s'),
                $purchaseAgg->purchase_due ?? 0,
            )
            : Money::round($purchaseAgg->purchase_due ?? 0);

        return [
            'sales_count' => (int) ($orderAgg->cnt ?? 0),
            'sales_amount' => Money::round($orderAgg->sales_amount ?? 0),
            'discount_amount' => Money::round($orderAgg->discount_amount ?? 0),
            'cash_collection' => $cashCollection,
            'expense' => $expense,
            'gross_profit' => $grossProfit,
            'net_profit' => $netProfit,
            'purchase_amount' => Money::round($purchaseAgg->purchase_amount ?? 0),
            'supplier_payment' => $supplierPayment,
            'return_amount' => $returns,
            'customer_due' => $customerDue,
            'supplier_payable' => $supplierPayable,
        ];
    }

    /**
     * Single-grouped SQL trend series (replaces 244-query legacy loop).
     *
     * @return array{labels: list<string>, sales: list<float>, profit: list<float>}
     */
    private function trendSeries(string $filterType, ?string $start, ?string $end, bool $includeProfit): array
    {
        $today = Carbon::today();
        if ($filterType === 'today') {
            $tStart = $today->copy()->subDays(6);
            $tEnd = $today->copy();
        } elseif ($filterType === 'all_time' || ! $start || ! $end) {
            $tStart = $today->copy()->subDays(29);
            $tEnd = $today->copy();
        } else {
            $tStart = Carbon::parse($start);
            $tEnd = Carbon::parse($end);
            if ($tStart->diffInDays($tEnd) > 60) {
                $tStart = $tEnd->copy()->subDays(60);
            }
        }

        $sStr = $tStart->toDateString();
        $eStr = $tEnd->toDateString();

        $salesByDay = DB::table('orders')
            ->whereBetween(DB::raw('COALESCE(invoice_date, DATE(created_at))'), [$sStr, $eStr])
            ->selectRaw('COALESCE(invoice_date, DATE(created_at)) as d, COALESCE(SUM(sub_total), 0) as sales, COALESCE(SUM(discount_amount), 0) as disc')
            ->groupBy('d')
            ->get()
            ->keyBy('d');

        $marginByDay = collect();
        $expByDay = collect();
        if ($includeProfit) {
            $marginByDay = DB::table('order_details')
                ->join('orders', 'orders.id', '=', 'order_details.order_id')
                ->whereBetween(DB::raw('COALESCE(orders.invoice_date, DATE(orders.created_at))'), [$sStr, $eStr])
                ->selectRaw('COALESCE(orders.invoice_date, DATE(orders.created_at)) as d, COALESCE(SUM((CAST(order_details.selling_price AS DECIMAL(15,2)) - CAST(order_details.price AS DECIMAL(15,2))) * GREATEST(0, CAST(order_details.quantity AS DECIMAL(15,2)) - COALESCE(order_details.return_quantity,0))), 0) as m')
                ->groupBy('d')
                ->pluck('m', 'd');

            $expDateSql = DateFilter::dateSql('date');
            $expByDay = DB::table('expenses')
                ->whereBetween(DB::raw($expDateSql), [$sStr, $eStr])
                ->selectRaw("({$expDateSql}) as d, COALESCE(SUM(expense_amount), 0) as e")
                ->groupBy('d')
                ->pluck('e', 'd');
        }

        $labels = [];
        $sales = [];
        $profit = [];

        for ($cursor = $tStart->copy(); $cursor->lte($tEnd); $cursor->addDay()) {
            $d = $cursor->toDateString();
            $labels[] = $cursor->format('d M');
            $daySales = (float) Money::round($salesByDay->get($d)?->sales ?? 0);
            $sales[] = $daySales;
            if ($includeProfit) {
                $m = $marginByDay->get($d) ?? 0;
                $disc = $salesByDay->get($d)?->disc ?? 0;
                $exp = $expByDay->get($d) ?? 0;
                $profit[] = (float) Money::sub(Money::sub($m, $disc), $exp);
            }
        }

        return ['labels' => $labels, 'sales' => $sales, 'profit' => $profit];
    }

    /**
     * Authoritative Retail Opening Balance roll-forward for a target date (fixes FIN-07).
     */
    public function openingBalanceForDate(string $targetDate): string
    {
        $target = Carbon::parse($targetDate)->toDateString();
        $latest = RetailOpeningBalance::where('date', '<=', $target)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->first();

        if (! $latest) {
            return '0.00';
        }

        $baseDate = Carbon::parse($latest->date)->toDateString();
        $baseAmount = Money::round($latest->amount);

        if ($baseDate === $target) {
            return $baseAmount;
        }

        // Roll forward net cash flow from [baseDate, target) — includes manual day's own transactions!
        $endPrevDay = Carbon::parse($target)->subDay()->toDateString();
        $netFlow = $this->netCashFlowBetween($baseDate, $endPrevDay);

        return Money::add($baseAmount, $netFlow);
    }

    public function netCashFlowBetween(string $startDate, string $endDate): string
    {
        if ($startDate > $endDate) {
            return '0.00';
        }

        $salesCash = DateFilter::applyDateFilter(DB::table('order_payment_details'), 'due_collection_date', $startDate, $endDate)
            ->selectRaw('COALESCE(SUM(paid_amount), 0) as s')->value('s');
        $prevCustCash = DateFilter::applyDateFilter(DB::table('customer_payment_details'), 'due_collection_date', $startDate, $endDate)
            ->whereRaw('CAST(COALESCE(previous_due_amount, 0) AS DECIMAL(15,2)) > 0')
            ->selectRaw('COALESCE(SUM(CAST(COALESCE(paid_amount, 0) AS DECIMAL(15,2))), 0) as s')->value('s');

        $purchaseCash = DateFilter::applyDateFilter(DB::table('purchase_payment_details'), 'purchase_due_collection_date', $startDate, $endDate)
            ->selectRaw('COALESCE(SUM(paid_amount), 0) as s')->value('s');
        $prevSuppCash = DateFilter::applyDateFilter(DB::table('supplier_due_collections'), 'due_collection_date', $startDate, $endDate)
            ->whereRaw('CAST(COALESCE(purchase_payable_amount, 0) AS DECIMAL(15,2)) > 0')
            ->selectRaw('COALESCE(SUM(CAST(COALESCE(paid_amount, 0) AS DECIMAL(15,2))), 0) as s')->value('s');
        $expenses = DateFilter::applyDateFilter(DB::table('expenses'), 'date', $startDate, $endDate)
            ->selectRaw('COALESCE(SUM(expense_amount), 0) as s')->value('s');

        $inflow = Money::add($salesCash, $prevCustCash);
        $outflow = Money::add(Money::add($purchaseCash, $prevSuppCash), $expenses);

        return Money::sub($inflow, $outflow);
    }

    /**
     * Retail Daily Income & Expense Ledger (fixes FIN-01 double-count & FIN-05 missing purchase payments).
     *
     * @return array{opening_balance: string, total_inflow: string, total_outflow: string, closing_balance: string, entries: Collection<int, array<string, mixed>>}
     */
    public function dailyLedger(string $startDate, string $endDate): array
    {
        $opening = $this->openingBalanceForDate($startDate);
        $entries = collect();

        // 1. Inflows: Order Payments
        $orderPayments = DateFilter::applyDateFilter(
            RetailOrderPaymentDetail::with('order.customer')->where('paid_amount', '>', 0),
            'due_collection_date',
            $startDate,
            $endDate,
        )->get();

        foreach ($orderPayments as $p) {
            $entries->push([
                'date' => DateFilter::parseDateString($p->due_collection_date) ?? $startDate,
                'timestamp' => $p->created_at?->timestamp ?? 0,
                'type' => 'Sales Collection',
                'reference' => $p->order?->order_no ?? "Order #{$p->order_id}",
                'party' => $p->order?->customer?->customer_name ?? 'Walk-in Customer',
                'method' => $p->payment_method ?? 'Cash',
                'inflow' => Money::round($p->paid_amount),
                'outflow' => '0.00',
            ]);
        }

        // 2. Inflows: Previous-Due-Only Customer Collections (excludes invoice summary rows to avoid 2x count)
        $prevCustPayments = DateFilter::applyDateFilter(
            RetailCustomerPaymentDetail::with('customer')
                ->whereRaw('CAST(COALESCE(previous_due_amount,0) AS DECIMAL(15,2)) > 0')
                ->whereRaw('CAST(COALESCE(paid_amount,0) AS DECIMAL(15,2)) > 0'),
            'due_collection_date',
            $startDate,
            $endDate,
        )->get();

        foreach ($prevCustPayments as $cp) {
            $entries->push([
                'date' => DateFilter::parseDateString($cp->due_collection_date) ?? $startDate,
                'timestamp' => $cp->created_at?->timestamp ?? 0,
                'type' => 'Previous Due Collection',
                'reference' => $cp->customer?->customer_id ?? "Customer #{$cp->customer_id}",
                'party' => $cp->customer?->customer_name ?? 'Customer',
                'method' => $cp->payment_method ?? 'Cash',
                'inflow' => Money::round($cp->paid_amount),
                'outflow' => '0.00',
            ]);
        }

        // 3. Outflows: Purchase Payments (fixes FIN-05!)
        $purchasePayments = DateFilter::applyDateFilter(
            RetailPurchasePaymentDetail::with('purchase.supplier')->where('paid_amount', '>', 0),
            'purchase_due_collection_date',
            $startDate,
            $endDate,
        )->get();

        foreach ($purchasePayments as $pp) {
            $entries->push([
                'date' => DateFilter::parseDateString($pp->purchase_due_collection_date) ?? $startDate,
                'timestamp' => $pp->created_at?->timestamp ?? 0,
                'type' => 'Purchase Payment',
                'reference' => $pp->purchase?->purchase_id ?? "Purchase #{$pp->purchases_id}",
                'party' => $pp->purchase?->supplier?->name ?? 'Supplier',
                'method' => $pp->payment_method ?? 'Cash',
                'inflow' => '0.00',
                'outflow' => Money::round($pp->paid_amount),
            ]);
        }

        // 4. Outflows: Previous-Payable-Only Supplier Payments
        $prevSuppPayments = DateFilter::applyDateFilter(
            RetailSupplierDueCollection::with('supplier')
                ->whereRaw('CAST(COALESCE(purchase_payable_amount,0) AS DECIMAL(15,2)) > 0')
                ->whereRaw('CAST(COALESCE(paid_amount,0) AS DECIMAL(15,2)) > 0'),
            'due_collection_date',
            $startDate,
            $endDate,
        )->get();

        foreach ($prevSuppPayments as $sp) {
            $entries->push([
                'date' => DateFilter::parseDateString($sp->due_collection_date) ?? $startDate,
                'timestamp' => $sp->created_at?->timestamp ?? 0,
                'type' => 'Supplier Previous Due',
                'reference' => $sp->supplier?->supplier_id ?? "Supplier #{$sp->supplier_id}",
                'party' => $sp->supplier?->name ?? 'Supplier',
                'method' => $sp->payment_method ?? 'Cash',
                'inflow' => '0.00',
                'outflow' => Money::round($sp->paid_amount),
            ]);
        }

        // 5. Outflows: Operating Expenses
        $expenses = DateFilter::applyDateFilter(
            RetailExpense::with(['expenseType', 'staff'])->where('expense_amount', '>', 0),
            'date',
            $startDate,
            $endDate,
        )->get();

        foreach ($expenses as $e) {
            $entries->push([
                'date' => DateFilter::parseDateString($e->date) ?? $startDate,
                'timestamp' => $e->created_at?->timestamp ?? 0,
                'type' => $e->expenseType?->type_name ?? 'Expense',
                'reference' => "EXP-#{$e->id}",
                'party' => $e->staff?->name ?? ($e->expense_details ?: 'Operating Expense'),
                'method' => 'Cash',
                'inflow' => '0.00',
                'outflow' => Money::round($e->expense_amount),
            ]);
        }

        $sorted = $entries->sortBy(fn ($row) => [$row['date'], $row['timestamp']])->values();
        $running = $opening;
        $totalInflow = '0.00';
        $totalOutflow = '0.00';

        $withRunning = $sorted->map(function (array $row) use (&$running, &$totalInflow, &$totalOutflow): array {
            $totalInflow = Money::add($totalInflow, $row['inflow']);
            $totalOutflow = Money::add($totalOutflow, $row['outflow']);
            $running = Money::sub(Money::add($running, $row['inflow']), $row['outflow']);
            $row['running_balance'] = $running;

            return $row;
        });

        return [
            'opening_balance' => $opening,
            'total_inflow' => $totalInflow,
            'total_outflow' => $totalOutflow,
            'closing_balance' => $running,
            'entries' => $withRunning,
        ];
    }

    /**
     * Retail Sales Report (fixes FIN-06: multiplies price & selling_price by net quantity and removes Auth::id filter).
     *
     * @return array{rows: Collection<int, array<string, mixed>>, totals: array<string, string>}
     */
    public function salesReport(string $startDate, string $endDate): array
    {
        $orders = DateFilter::applyInvoiceDateFilter(
            RetailOrder::with(['customer', 'details', 'productReturns']),
            'invoice_date',
            $startDate,
            $endDate,
        )->orderByDesc('id')->get();

        $totalCost = '0.00';
        $totalSales = '0.00';
        $totalDiscount = '0.00';
        $totalDelivery = '0.00';
        $totalReturn = '0.00';
        $totalPaid = '0.00';
        $totalDue = '0.00';
        $totalProfit = '0.00';

        $rows = $orders->map(function (RetailOrder $o) use (&$totalCost, &$totalSales, &$totalDiscount, &$totalDelivery, &$totalReturn, &$totalPaid, &$totalDue, &$totalProfit): array {
            $cost = '0.00';
            $sell = '0.00';
            foreach ($o->details as $d) {
                $netQty = max(0, (int) round((float) $d->quantity) - (int) ($d->return_quantity ?? 0));
                $cost = Money::add($cost, Money::mul($d->price, $netQty));
                $sell = Money::add($sell, Money::mul($d->selling_price, $netQty));
            }
            $disc = Money::round($o->discount_amount ?? 0);
            $del = Money::round($o->delivery_charge ?? 0);
            $ret = Money::sum($o->productReturns->pluck('amount'));
            $paid = Money::round($o->paid_amount ?? 0);
            $due = Money::round($o->due_amount ?? 0);
            $profit = Money::sub(Money::sub($sell, $cost), $disc);

            $totalCost = Money::add($totalCost, $cost);
            $totalSales = Money::add($totalSales, $sell);
            $totalDiscount = Money::add($totalDiscount, $disc);
            $totalDelivery = Money::add($totalDelivery, $del);
            $totalReturn = Money::add($totalReturn, $ret);
            $totalPaid = Money::add($totalPaid, $paid);
            $totalDue = Money::add($totalDue, $due);
            $totalProfit = Money::add($totalProfit, $profit);

            return [
                'id' => $o->id,
                'order_no' => $o->order_no,
                'date' => $o->invoice_date ?? $o->created_at?->toDateString(),
                'customer_name' => $o->customer?->customer_name ?? 'N/A',
                'total_cost' => $cost,
                'selling_price' => $sell,
                'discount_amount' => $disc,
                'delivery_charge' => $del,
                'return_amount' => $ret,
                'paid_amount' => $paid,
                'due_amount' => $due,
                'profit' => $profit,
            ];
        });

        return [
            'rows' => $rows,
            'totals' => [
                'total_cost' => $totalCost,
                'selling_price' => $totalSales,
                'discount_amount' => $totalDiscount,
                'delivery_charge' => $totalDelivery,
                'return_amount' => $totalReturn,
                'paid_amount' => $totalPaid,
                'due_amount' => $totalDue,
                'profit' => $totalProfit,
            ],
        ];
    }

    /**
     * Retail Daily Receipt & Payment Statement (fixes FIN-07 exact Closing Balance reconciliation).
     *
     * @return array<string, mixed>
     */
    public function dailyReceiptPayment(string $startDate, string $endDate): array
    {
        $opening = $this->openingBalanceForDate($startDate);

        $salesCollection = Money::round(
            DateFilter::applyDateFilter(DB::table('order_payment_details'), 'due_collection_date', $startDate, $endDate)
                ->selectRaw('COALESCE(SUM(paid_amount), 0) as s')->value('s')
        );
        $prevDueCollection = Money::round(
            DateFilter::applyDateFilter(DB::table('customer_payment_details'), 'due_collection_date', $startDate, $endDate)
                ->whereRaw('CAST(COALESCE(previous_due_amount,0) AS DECIMAL(15,2)) > 0')
                ->selectRaw('COALESCE(SUM(CAST(COALESCE(paid_amount,0) AS DECIMAL(15,2))), 0) as s')->value('s')
        );
        $totalReceipts = Money::add($salesCollection, $prevDueCollection);

        // Supplier Payments grouped by supplier (purchase payments + previous-payable-only collections)
        $purchasePayGrouped = DateFilter::applyDateFilter(
            DB::table('purchase_payment_details')
                ->join('purchases', 'purchases.id', '=', 'purchase_payment_details.purchases_id')
                ->join('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
                ->where('purchase_payment_details.paid_amount', '>', 0),
            'purchase_payment_details.purchase_due_collection_date',
            $startDate,
            $endDate,
        )->selectRaw('suppliers.id, suppliers.name, COALESCE(SUM(purchase_payment_details.paid_amount), 0) as total')
            ->groupBy('suppliers.id', 'suppliers.name')
            ->get();

        $prevSuppGrouped = DateFilter::applyDateFilter(
            DB::table('supplier_due_collections')
                ->join('suppliers', 'suppliers.id', '=', 'supplier_due_collections.supplier_id')
                ->whereRaw('CAST(COALESCE(supplier_due_collections.purchase_payable_amount,0) AS DECIMAL(15,2)) > 0')
                ->whereRaw('CAST(COALESCE(supplier_due_collections.paid_amount,0) AS DECIMAL(15,2)) > 0'),
            'supplier_due_collections.due_collection_date',
            $startDate,
            $endDate,
        )->selectRaw('suppliers.id, suppliers.name, COALESCE(SUM(CAST(supplier_due_collections.paid_amount AS DECIMAL(15,2))), 0) as total')
            ->groupBy('suppliers.id', 'suppliers.name')
            ->get();

        $supplierMap = [];
        foreach ($purchasePayGrouped->concat($prevSuppGrouped) as $row) {
            $supplierMap[$row->name] = Money::add($supplierMap[$row->name] ?? '0.00', $row->total);
        }
        $totalPaidToSupplier = Money::sum($supplierMap);

        $expensesGrouped = DateFilter::applyDateFilter(
            DB::table('expenses')
                ->join('expense_types', 'expense_types.id', '=', 'expenses.expense_type_id')
                ->where('expenses.expense_amount', '>', 0),
            'expenses.date',
            $startDate,
            $endDate,
        )->selectRaw('expense_types.type_name, COALESCE(SUM(expenses.expense_amount), 0) as total')
            ->groupBy('expense_types.type_name')
            ->pluck('total', 'type_name')
            ->map(fn ($v) => Money::round($v))
            ->all();
        $totalExpense = Money::sum($expensesGrouped);

        $totalPayments = Money::add($totalPaidToSupplier, $totalExpense);
        $closing = Money::sub(Money::add($opening, $totalReceipts), $totalPayments);

        return [
            'opening_balance' => $opening,
            'sales_collection' => $salesCollection,
            'previous_due_collection' => $prevDueCollection,
            'total_receipts' => $totalReceipts,
            'supplier_payments' => $supplierMap,
            'total_paid_to_supplier' => $totalPaidToSupplier,
            'expenses' => $expensesGrouped,
            'total_expense' => $totalExpense,
            'total_payments' => $totalPayments,
            'closing_balance' => $closing,
        ];
    }

    /**
     * Retail Income & Expense Summary grouped by date.
     *
     * @return array{rows: Collection<int, array<string, string>>, totals: array<string, string>}
     */
    public function incomeExpenseSummary(string $startDate, string $endDate): array
    {
        $sales = DateFilter::applyInvoiceDateFilter(
            DB::table('order_details')->join('orders', 'orders.id', '=', 'order_details.order_id'),
            'orders.invoice_date',
            $startDate,
            $endDate,
            'orders.created_at',
        )->selectRaw('
            COALESCE(orders.invoice_date, DATE(orders.created_at)) as d,
            COALESCE(SUM(CAST(order_details.price AS DECIMAL(15,2)) * GREATEST(0, CAST(order_details.quantity AS DECIMAL(15,2)) - COALESCE(order_details.return_quantity,0))), 0) as cost_amt,
            COALESCE(SUM(CAST(order_details.selling_price AS DECIMAL(15,2)) * GREATEST(0, CAST(order_details.quantity AS DECIMAL(15,2)) - COALESCE(order_details.return_quantity,0))), 0) as sell_amt
        ')->groupBy('d')->get()->keyBy('d');

        $orderTotals = DateFilter::applyInvoiceDateFilter(DB::table('orders'), 'invoice_date', $startDate, $endDate)
            ->selectRaw('COALESCE(invoice_date, DATE(created_at)) as d, COALESCE(SUM(paid_amount),0) as paid, COALESCE(SUM(discount_amount),0) as disc, COALESCE(SUM(due_amount),0) as due')
            ->groupBy('d')->get()->keyBy('d');

        $retSql = DateFilter::dateSql('date');
        $returns = DB::table('product_returns')
            ->whereBetween(DB::raw($retSql), [$startDate, $endDate])
            ->selectRaw("({$retSql}) as d, COALESCE(SUM(CAST(amount AS DECIMAL(15,2))), 0) as amt")
            ->groupBy('d')->pluck('amt', 'd');

        $expSql = DateFilter::dateSql('date');
        $expenses = DB::table('expenses')
            ->whereBetween(DB::raw($expSql), [$startDate, $endDate])
            ->selectRaw("({$expSql}) as d, COALESCE(SUM(expense_amount), 0) as amt")
            ->groupBy('d')->pluck('amt', 'd');

        $dates = collect()
            ->merge($sales->keys())
            ->merge($orderTotals->keys())
            ->merge($returns->keys())
            ->merge($expenses->keys())
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();

        $totals = ['cost' => '0.00', 'sales' => '0.00', 'paid' => '0.00', 'discount' => '0.00', 'due' => '0.00', 'return' => '0.00', 'expense' => '0.00', 'net_profit' => '0.00'];

        $rows = $dates->map(function (string $d) use ($sales, $orderTotals, $returns, $expenses, &$totals): array {
            $cost = Money::round($sales->get($d)?->cost_amt ?? 0);
            $sell = Money::round($sales->get($d)?->sell_amt ?? 0);
            $paid = Money::round($orderTotals->get($d)?->paid ?? 0);
            $disc = Money::round($orderTotals->get($d)?->disc ?? 0);
            $due = Money::round($orderTotals->get($d)?->due ?? 0);
            $ret = Money::round($returns->get($d) ?? 0);
            $exp = Money::round($expenses->get($d) ?? 0);
            $netProfit = Money::sub(Money::sub(Money::sub($sell, $cost), $disc), $exp);

            $totals['cost'] = Money::add($totals['cost'], $cost);
            $totals['sales'] = Money::add($totals['sales'], $sell);
            $totals['paid'] = Money::add($totals['paid'], $paid);
            $totals['discount'] = Money::add($totals['discount'], $disc);
            $totals['due'] = Money::add($totals['due'], $due);
            $totals['return'] = Money::add($totals['return'], $ret);
            $totals['expense'] = Money::add($totals['expense'], $exp);
            $totals['net_profit'] = Money::add($totals['net_profit'], $netProfit);

            return [
                'date' => $d,
                'cost' => $cost,
                'sales' => $sell,
                'paid' => $paid,
                'discount' => $disc,
                'due' => $due,
                'return' => $ret,
                'expense' => $exp,
                'net_profit' => $netProfit,
            ];
        });

        return ['rows' => $rows, 'totals' => $totals];
    }

    /**
     * Retail Personal & Investor Transaction Report.
     *
     * @return array{expenses: Collection, invests: Collection, total_expense: string, total_invest: string}
     */
    public function personalTransactionReport(string $startDate, string $endDate): array
    {
        $expenses = DateFilter::applyDateFilter(
            RetailExpense::with('expenseType')->whereHas('expenseType', fn ($q) => $q->where('type_name', 'like', '%Personal%')),
            'date',
            $startDate,
            $endDate,
        )->orderByDesc('id')->get();

        $invests = DateFilter::applyDateFilter(
            RetailInvest::with('investor'),
            'date',
            $startDate,
            $endDate,
        )->orderByDesc('id')->get();

        return [
            'expenses' => $expenses,
            'invests' => $invests,
            'total_expense' => Money::sum($expenses->pluck('expense_amount')),
            'total_invest' => Money::sum($invests->pluck('invest_amount')),
        ];
    }

    /**
     * Retail Best-Selling Products Report (fixes FIN-06: multiplies by net quantity!).
     *
     * @return Collection<int, object>
     */
    public function bestSellingProducts(string $startDate, string $endDate): Collection
    {
        return DateFilter::applyInvoiceDateFilter(
            DB::table('order_details')
                ->join('orders', 'orders.id', '=', 'order_details.order_id')
                ->join('products', 'products.id', '=', 'order_details.product_id')
                ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
                ->leftJoin('categories', 'categories.id', '=', 'products.category_id'),
            'orders.invoice_date',
            $startDate,
            $endDate,
            'orders.created_at',
        )->selectRaw('
            products.id,
            products.product_name,
            brands.name as brand_name,
            categories.category_name,
            SUM(GREATEST(0, CAST(order_details.quantity AS DECIMAL(15,2)) - COALESCE(order_details.return_quantity,0))) as total_quantity,
            SUM(CAST(order_details.price AS DECIMAL(15,2)) * GREATEST(0, CAST(order_details.quantity AS DECIMAL(15,2)) - COALESCE(order_details.return_quantity,0))) as total_cost,
            SUM(CAST(order_details.selling_price AS DECIMAL(15,2)) * GREATEST(0, CAST(order_details.quantity AS DECIMAL(15,2)) - COALESCE(order_details.return_quantity,0))) as total_sales
        ')
            ->groupBy('products.id', 'products.product_name', 'brands.name', 'categories.category_name')
            ->orderByDesc('total_quantity')
            ->get();
    }
}
