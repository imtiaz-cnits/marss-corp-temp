<?php

namespace App\Http\Controllers\Retail;

use App\Http\Controllers\Controller;
use App\Models\Retail\RetailOrder;
use App\Models\Retail\RetailProductReturn;
use App\Models\Retail\RetailPurchase;
use App\Models\Retail\RetailPurchaseReturn;
use App\Services\Retail\RetailDueCollectionService;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\View\View;

class RetailPrintController extends Controller
{
    public function invoice(int $id, RetailDueCollectionService $dueService): View
    {
        $order = RetailOrder::with(['customer', 'details.product', 'payments', 'productReturns'])->findOrFail($id);

        return $this->renderInvoice($order, $dueService);
    }

    public function latestReceipt(RetailDueCollectionService $dueService): View
    {
        $order = RetailOrder::with(['customer', 'details.product', 'payments', 'productReturns'])
            ->orderByDesc('id')
            ->firstOrFail();

        return $this->renderInvoice($order, $dueService);
    }

    private function renderInvoice(RetailOrder $order, RetailDueCollectionService $dueService): View
    {
        $bal = $order->customer_id ? $dueService->customerBalance((int) $order->customer_id) : ['total_due' => '0.00'];
        $otherDue = Money::maxZero(Money::sub($bal['total_due'], $order->due_amount ?? 0));
        $returnedAmt = '0.00';
        $lines = [];

        foreach ($order->details as $d) {
            $qty = (int) round((float) $d->quantity);
            $retQty = (int) ($d->return_quantity ?? 0);
            $rate = Money::round($d->selling_price ?? $d->price ?? 0);
            if ($retQty > 0) {
                $returnedAmt = Money::add($returnedAmt, Money::mul($rate, $retQty));
            }
            $lines[] = [
                'name' => (string) ($d->product?->product_name ?? 'Product'),
                'door_side' => $d->product?->door_side,
                'qty' => $qty,
                'return_qty' => $retQty,
                'rate' => $rate,
                'total' => Money::mul($rate, $qty),
            ];
        }

        return view('print.marss-invoice', [
            'moduleLabel' => 'Retail Shop',
            'tagline' => 'All Kinds of Hardware, Doors &amp; Building Materials Supplier',
            'backUrl' => route('retail.invoices'),
            'docNo' => $order->order_no ?: ('#InvID'.str_pad((string) $order->id, 5, '0', STR_PAD_LEFT)),
            'challanNo' => (string) ($order->order_note ?? ''),
            'date' => Carbon::parse($order->invoice_date ?? $order->created_at)->format('d-m-Y'),
            'partyName' => $order->customer?->customer_name ?? 'Walk-in Customer',
            'partyAddress' => $order->customer?->address_details ?? '',
            'partyMobile' => $order->customer?->mobile ?? '',
            'lines' => $lines,
            'subTotal' => Money::round($order->sub_total ?? 0),
            'discountAmount' => Money::round($order->discount_amount ?? 0),
            'discountType' => $order->discount_type ?? 'fixed',
            'discountRate' => $order->discount_rate,
            'deliveryCharge' => Money::round($order->delivery_charge ?? 0),
            'returnedAmount' => $returnedAmt,
            'paidAmount' => Money::round($order->paid_amount ?? 0),
            'dueAmount' => Money::round($order->due_amount ?? 0),
            'previousDue' => $otherDue,
        ]);
    }

    public function purchase(int $id, RetailDueCollectionService $dueService): View
    {
        $purchase = RetailPurchase::with(['supplier', 'orderDetails.product', 'paymentDetails'])->findOrFail($id);

        $lines = $purchase->orderDetails->map(fn ($d) => [
            'name' => (string) ($d->product?->product_name ?? 'Product'),
            'qty' => (int) round((float) $d->quantity),
            'return_qty' => (int) ($d->return_quantity ?? 0),
            'rate' => Money::round($d->cost_price ?? 0),
            'total' => Money::round($d->subtotal ?? Money::mul($d->cost_price ?? 0, $d->quantity ?? 0)),
        ])->all();

        $bal = $purchase->supplier_id ? $dueService->supplierBalance((int) $purchase->supplier_id) : ['total_due' => '0.00'];
        $dueAmt = Money::round($purchase->due_amount ?? 0);
        $paidAmt = Money::round($purchase->paid_amount ?? 0);
        $previousDue = Money::maxZero(Money::sub($bal['total_due'], $dueAmt));
        $paymentStatus = Money::cmp($dueAmt, 0) <= 0 ? 'Fully Paid' : (Money::cmp($paidAmt, 0) > 0 ? 'Partial Paid' : 'Unpaid');

        return view('print.marss-purchase', [
            'title' => 'Purchase Details ' . $purchase->purchase_id,
            'moduleLabel' => 'Retail & Wholesale Management System',
            'tagline' => 'All Kinds of Dry & Gel Battery Supplier',
            'backUrl' => route('retail.purchases'),
            'docNo' => $purchase->purchase_id,
            'refNo' => (string) ($purchase->referance_no ?? ''),
            'date' => Carbon::parse($purchase->date ?? $purchase->created_at)->format('d-m-Y'),
            'supplierName' => $purchase->supplier?->name ?? 'N/A',
            'supplierCompany' => $purchase->supplier?->company ?? '',
            'supplierMobile' => $purchase->supplier?->mobile ?? 'N/A',
            'supplierAddress' => $purchase->supplier?->address ?? 'N/A',
            'lines' => $lines,
            'subTotal' => Money::round($purchase->grand_subtotal ?? 0),
            'discountAmount' => Money::round($purchase->discount_amount ?? 0),
            'discountType' => $purchase->discount_type ?? 'fixed',
            'discountRate' => $purchase->discount_rate,
            'deliveryCharge' => Money::round($purchase->delivery_charge ?? 0),
            'returnAdjustment' => Money::round($purchase->return_adjustment_amount ?? 0),
            'paidAmount' => $paidAmt,
            'dueAmount' => $dueAmt,
            'previousDue' => $previousDue,
            'paymentStatus' => $paymentStatus,
        ]);
    }

    public function salesReturn(int $id): View
    {
        $ret = RetailProductReturn::with(['order', 'customer', 'product'])->findOrFail($id);
        $batch = RetailProductReturn::with('product')->where('return_number', $ret->return_number)->get();

        return view('print.marss-return', [
            'title' => 'Retail Sales Return Voucher',
            'backUrl' => route('retail.returns'),
            'returnNo' => $ret->return_number,
            'refNo' => $ret->order?->order_no ?? "Order #{$ret->order_id}",
            'date' => Carbon::parse($ret->date ?? $ret->created_at)->format('d-m-Y'),
            'partyLabel' => 'Customer',
            'partyName' => $ret->customer?->customer_name ?? 'Customer',
            'partyMobile' => $ret->customer?->mobile ?? '',
            'lines' => $batch->map(fn ($r) => [
                'name' => $r->product?->product_name ?? 'Product',
                'qty' => (int) $r->quantity,
                'rate' => Money::round($r->unit_price),
                'amount' => Money::round($r->amount),
            ])->all(),
            'totalAmount' => Money::sum($batch->pluck('amount')),
            'totalDueReduced' => Money::sum($batch->pluck('due_amount')),
        ]);
    }

    public function purchaseReturn(int $id): View
    {
        $ret = RetailPurchaseReturn::with(['purchase', 'supplier', 'product'])->findOrFail($id);
        $batch = RetailPurchaseReturn::with('product')->where('return_number', $ret->return_number)->get();

        return view('print.marss-return', [
            'title' => 'Retail Purchase Return Voucher',
            'backUrl' => route('retail.returns'),
            'returnNo' => $ret->return_number,
            'refNo' => $ret->purchase?->purchase_id ?? "Purchase #{$ret->purchase_id}",
            'date' => Carbon::parse($ret->date ?? $ret->created_at)->format('d-m-Y'),
            'partyLabel' => 'Supplier',
            'partyName' => $ret->supplier?->name ?? 'Supplier',
            'partyMobile' => $ret->supplier?->mobile ?? '',
            'lines' => $batch->map(fn ($r) => [
                'name' => $r->product?->product_name ?? 'Product',
                'qty' => (int) $r->quantity,
                'rate' => Money::round($r->unit_price),
                'amount' => Money::round($r->amount),
            ])->all(),
            'totalAmount' => Money::sum($batch->pluck('amount')),
            'totalDueReduced' => Money::sum($batch->pluck('due_amount')),
        ]);
    }
}
