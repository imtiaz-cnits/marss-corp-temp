<?php

namespace App\Http\Controllers\Battery;

use App\Http\Controllers\Controller;
use App\Models\Battery\BatteryOrder;
use App\Models\Battery\BatteryProductReturn;
use App\Models\Battery\BatteryPurchase;
use App\Models\Battery\BatteryPurchaseReturn;
use App\Services\Battery\BatteryDueCollectionService;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\View\View;

class BatteryPrintController extends Controller
{
    public function invoice(int $id, BatteryDueCollectionService $dueService): View
    {
        $order = BatteryOrder::with(['customer', 'details.product', 'payments', 'productReturns'])->findOrFail($id);
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
                'name' => (string) ($d->product?->product_name ?? 'Battery Product'),
                'door_side' => null,
                'qty' => $qty,
                'return_qty' => $retQty,
                'rate' => $rate,
                'total' => Money::mul($rate, $qty),
            ];
        }

        return view('print.marss-invoice', [
            'moduleLabel' => 'Battery Division',
            'tagline' => 'All Kinds of Dry &amp; Gel Battery Supplier',
            'backUrl' => route('battery.invoices'),
            'docNo' => $order->order_no ?: ('#BInv'.str_pad((string) $order->id, 5, '0', STR_PAD_LEFT)),
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

    public function purchase(int $id, BatteryDueCollectionService $dueService): View
    {
        $purchase = BatteryPurchase::with(['supplier', 'orderDetails.product', 'paymentDetails'])->findOrFail($id);

        $lines = $purchase->orderDetails->map(fn ($d) => [
            'name' => (string) ($d->product?->product_name ?? 'Battery Product'),
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
            'backUrl' => route('battery.purchases'),
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
        $ret = BatteryProductReturn::with(['order', 'customer', 'product'])->findOrFail($id);
        $batch = BatteryProductReturn::with('product')->where('return_number', $ret->return_number)->get();

        return view('print.marss-return', [
            'title' => 'Battery Sales Return Voucher',
            'backUrl' => route('battery.sales.returns'),
            'returnNo' => $ret->return_number,
            'refNo' => $ret->order?->order_no ?? "Order #{$ret->order_id}",
            'date' => Carbon::parse($ret->date ?? $ret->created_at)->format('d-m-Y'),
            'partyLabel' => 'Customer',
            'partyName' => $ret->customer?->customer_name ?? 'Customer',
            'partyMobile' => $ret->customer?->mobile ?? '',
            'lines' => $batch->map(fn ($r) => [
                'name' => $r->product?->product_name ?? 'Battery Product',
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
        $ret = BatteryPurchaseReturn::with(['purchase', 'supplier', 'product'])->findOrFail($id);
        $batch = BatteryPurchaseReturn::with('product')->where('return_number', $ret->return_number)->get();

        return view('print.marss-return', [
            'title' => 'Battery Purchase Return Voucher',
            'backUrl' => route('battery.purchase.returns'),
            'returnNo' => $ret->return_number,
            'refNo' => $ret->purchase?->purchase_id ?? "Purchase #{$ret->purchase_id}",
            'date' => Carbon::parse($ret->date ?? $ret->created_at)->format('d-m-Y'),
            'partyLabel' => 'Supplier',
            'partyName' => $ret->supplier?->name ?? 'Supplier',
            'partyMobile' => $ret->supplier?->mobile ?? '',
            'lines' => $batch->map(fn ($r) => [
                'name' => $r->product?->product_name ?? 'Battery Product',
                'qty' => (int) $r->quantity,
                'rate' => Money::round($r->unit_price),
                'amount' => Money::round($r->amount),
            ])->all(),
            'totalAmount' => Money::sum($batch->pluck('amount')),
            'totalDueReduced' => Money::sum($batch->pluck('due_amount')),
        ]);
    }
}
