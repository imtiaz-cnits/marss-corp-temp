<?php

namespace App\Services\Retail;

use App\Models\Retail\RetailCustomer;
use App\Models\Retail\RetailOrder;
use App\Models\Retail\RetailProduct;
use App\Models\Retail\RetailPurchase;
use App\Models\Retail\RetailSupplier;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

/**
 * Independent Retail Financial Reconciliation Service (Section 8.6).
 * Performs read-only database-level verification of Retail accounting invariants.
 */
class FinancialReconciliationService
{
    public function __construct(
        private readonly RetailReportService $reportService,
    ) {}

    /**
     * Execute full Retail reconciliation audit.
     *
     * @return array<string, mixed>
     */
    public function reconcile(): array
    {
        return [
            'module' => 'retail',
            'purchases' => $this->reconcilePurchases(),
            'orders' => $this->reconcileOrders(),
            'stock' => $this->reconcileStock(),
            'customers' => $this->reconcileCustomers(),
            'suppliers' => $this->reconcileSuppliers(),
            'ledger_check' => $this->reconcileDailyLedgerBalance(),
        ];
    }

    /**
     * @return array{total: int, reconciled: int, discrepancies: list<array<string, mixed>>}
     */
    public function reconcilePurchases(): array
    {
        $purchases = RetailPurchase::with(['orderDetails', 'paymentDetails'])->get();
        $discrepancies = [];

        foreach ($purchases as $p) {
            $linesSum = Money::sum($p->orderDetails->map(fn ($d) => Money::mul($d->cost_price, $d->quantity)));
            $paySum = Money::sum($p->paymentDetails->pluck('paid_amount'));
            $grand = Money::round($p->grand_subtotal ?? 0);
            $net = Money::maxZero(Money::sub(
                Money::add(Money::sub($grand, $p->discount_amount ?? 0), $p->delivery_charge ?? 0),
                $p->return_adjustment_amount ?? 0,
            ));
            $expectedDue = Money::maxZero(Money::sub($net, $p->paid_amount ?? 0));

            $issues = [];
            if (Money::cmp(Money::sub($grand, $linesSum), '0.05') > 0 || Money::cmp(Money::sub($linesSum, $grand), '0.05') > 0) {
                $issues[] = "grand_subtotal({$grand}) != lines({$linesSum})";
            }
            if (Money::cmp(Money::sub($p->paid_amount ?? 0, $paySum), '0.05') > 0 || Money::cmp(Money::sub($paySum, $p->paid_amount ?? 0), '0.05') > 0) {
                $issues[] = "paid({$p->paid_amount}) != payments_sum({$paySum})";
            }
            if (Money::cmp(Money::sub($p->due_amount ?? 0, $expectedDue), '0.05') > 0 || Money::cmp(Money::sub($expectedDue, $p->due_amount ?? 0), '0.05') > 0) {
                $issues[] = "due({$p->due_amount}) != expected_due({$expectedDue})";
            }

            if ($issues) {
                $discrepancies[] = ['id' => $p->id, 'purchase_id' => $p->purchase_id, 'issues' => $issues];
            }
        }

        return [
            'total' => $purchases->count(),
            'reconciled' => $purchases->count() - count($discrepancies),
            'discrepancies' => $discrepancies,
        ];
    }

    /**
     * @return array{total: int, reconciled: int, discrepancies: list<array<string, mixed>>}
     */
    public function reconcileOrders(): array
    {
        $orders = RetailOrder::with(['details', 'payments', 'productReturns'])->get();
        $discrepancies = [];

        foreach ($orders as $o) {
            $linesSum = Money::sum($o->details->map(fn ($d) => Money::mul($d->selling_price, $d->quantity)));
            $paySum = Money::sum($o->payments->pluck('paid_amount'));
            $payDiscSum = Money::sum($o->payments->pluck('discount_amount'));
            $sub = Money::round($o->sub_total ?? 0);
            $net = Money::maxZero(Money::sub(
                Money::add(Money::sub($sub, $o->discount_amount ?? 0), $o->delivery_charge ?? 0),
                Money::add($o->return_adjustment_amount ?? 0, $payDiscSum),
            ));
            $expectedDue = Money::maxZero(Money::sub($net, $o->paid_amount ?? 0));

            $issues = [];
            if (abs((float) Money::sub($sub, $linesSum)) > 0.05) {
                $issues[] = "sub_total({$sub}) != lines({$linesSum})";
            }
            if (abs((float) Money::sub($o->paid_amount ?? 0, $paySum)) > 0.05) {
                $issues[] = "paid({$o->paid_amount}) != payments_sum({$paySum})";
            }
            if (abs((float) Money::sub($o->due_amount ?? 0, $expectedDue)) > 0.05) {
                $issues[] = "due({$o->due_amount}) != expected_due({$expectedDue})";
            }

            if ($issues) {
                $discrepancies[] = ['id' => $o->id, 'order_no' => $o->order_no, 'issues' => $issues];
            }
        }

        return [
            'total' => $orders->count(),
            'reconciled' => $orders->count() - count($discrepancies),
            'discrepancies' => $discrepancies,
        ];
    }

    /**
     * @return array{total_products: int, negative_stock_count: int, total_units: int, cost_valuation: string}
     */
    public function reconcileStock(): array
    {
        $negCount = RetailProduct::whereRaw('CAST(COALESCE(quantity,0) AS SIGNED) < 0')->count();
        $totalUnits = (int) DB::table('products')->selectRaw('COALESCE(SUM(CAST(COALESCE(quantity,0) AS SIGNED)), 0) as s')->value('s');
        $costVal = Money::round(
            DB::table('products')
                ->whereRaw('CAST(COALESCE(quantity,0) AS SIGNED) > 0')
                ->selectRaw('COALESCE(SUM(CAST(COALESCE(quantity,0) AS DECIMAL(15,2)) * CAST(COALESCE(cost_price,0) AS DECIMAL(15,2))), 0) as v')
                ->value('v')
        );

        return [
            'total_products' => RetailProduct::count(),
            'negative_stock_count' => $negCount,
            'total_units' => $totalUnits,
            'cost_valuation' => $costVal,
        ];
    }

    /**
     * @return array{total_customers: int, total_receivable: string}
     */
    public function reconcileCustomers(): array
    {
        $prev = DB::table('customers')->selectRaw('COALESCE(SUM(CAST(COALESCE(previous_due_amount,0) AS DECIMAL(15,2))),0) as s')->value('s');
        $orders = DB::table('orders')->selectRaw('COALESCE(SUM(CAST(COALESCE(due_amount,0) AS DECIMAL(15,2))),0) as s')->value('s');

        return [
            'total_customers' => RetailCustomer::count(),
            'total_receivable' => Money::add($prev, $orders),
        ];
    }

    /**
     * @return array{total_suppliers: int, total_payable: string}
     */
    public function reconcileSuppliers(): array
    {
        $prev = DB::table('suppliers')->selectRaw('COALESCE(SUM(CAST(COALESCE(purchase_payable_amount,0) AS DECIMAL(15,2))),0) as s')->value('s');
        $purchases = DB::table('purchases')->selectRaw('COALESCE(SUM(CAST(COALESCE(due_amount,0) AS DECIMAL(15,2))),0) as s')->value('s');

        return [
            'total_suppliers' => RetailSupplier::count(),
            'total_payable' => Money::add($prev, $purchases),
        ];
    }

    /**
     * Verifies Daily Ledger and Daily Receipt & Payment produce identical Opening + Inflow - Outflow = Closing.
     *
     * @return array{balanced: bool, ledger_closing: string, receipt_payment_closing: string}
     */
    public function reconcileDailyLedgerBalance(): array
    {
        $start = '2026-01-01';
        $end = now()->toDateString();

        $ledger = $this->reportService->dailyLedger($start, $end);
        $rp = $this->reportService->dailyReceiptPayment($start, $end);

        return [
            'balanced' => Money::cmp($ledger['closing_balance'], $rp['closing_balance']) === 0,
            'ledger_closing' => $ledger['closing_balance'],
            'receipt_payment_closing' => $rp['closing_balance'],
        ];
    }
}
