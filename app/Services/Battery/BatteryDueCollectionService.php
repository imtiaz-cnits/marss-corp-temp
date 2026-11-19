<?php

namespace App\Services\Battery;

use App\Models\Battery\BatteryCustomer;
use App\Models\Battery\BatteryCustomerPaymentDetail;
use App\Models\Battery\BatteryOrder;
use App\Models\Battery\BatteryOrderPaymentDetail;
use App\Models\Battery\BatteryPurchase;
use App\Models\Battery\BatteryPurchasePaymentDetail;
use App\Models\Battery\BatterySupplier;
use App\Models\Battery\BatterySupplierDueCollection;
use App\Services\Shared\ActivityLogger;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * 100% Isolated Battery Customer & Supplier FIFO Due Collection Service.
 */
class BatteryDueCollectionService
{
    /**
     * @return array{applied_previous: string, applied_invoices: string, remaining_total_due: string}
     */
    public function collectCustomerDue(int $customerId, array $data, int $userId): array
    {
        $availPaid = Money::maxZero($data['paid_amount'] ?? 0);
        $availDiscount = Money::maxZero($data['discount_amount'] ?? 0);
        $collectionType = in_array($data['collection_type'] ?? 'all', ['all', 'previous', 'invoice'], true)
            ? ($data['collection_type'] ?? 'all')
            : 'all';

        if (Money::cmp(Money::add($availPaid, $availDiscount), 0) <= 0) {
            throw new InvalidArgumentException('Collection amount or discount must be greater than zero.');
        }

        return DB::transaction(function () use ($customerId, $availPaid, $availDiscount, $collectionType, $data, $userId): array {
            $customer = BatteryCustomer::lockForUpdate()->findOrFail($customerId);
            $dueOrders = BatteryOrder::where('customer_id', $customer->id)
                ->whereRaw('CAST(COALESCE(due_amount, 0) AS DECIMAL(15,2)) > 0')
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->sortBy(fn ($o) => [$o->invoice_date ?? $o->created_at?->toDateString() ?? '', $o->id])
                ->values();

            $date = Carbon::parse($data['date'] ?? now())->toDateString();
            $method = $data['payment_method'] ?? 'Cash';
            $trxId = $data['transaction_id'] ?? null;

            $appliedPrev = '0.00';
            $appliedInvoices = '0.00';

            $customerPrevDue = Money::maxZero($customer->previous_due_amount ?? 0);
            if (($collectionType === 'all' || $collectionType === 'previous') && Money::cmp($customerPrevDue, 0) > 0) {
                $power = Money::add($availPaid, $availDiscount);
                if (Money::cmp($power, $customerPrevDue) >= 0) {
                    $usedDisc = Money::min($availDiscount, $customerPrevDue);
                    $usedPaid = Money::sub($customerPrevDue, $usedDisc);
                    $applied = $customerPrevDue;
                    $customer->previous_due_amount = '0.00';
                    $availDiscount = Money::sub($availDiscount, $usedDisc);
                    $availPaid = Money::sub($availPaid, $usedPaid);
                } else {
                    $usedPaid = $availPaid;
                    $usedDisc = $availDiscount;
                    $applied = $power;
                    $customer->previous_due_amount = Money::maxZero(Money::sub($customerPrevDue, $applied));
                    $availPaid = '0.00';
                    $availDiscount = '0.00';
                }
                $customer->save();
                $appliedPrev = $applied;

                $remainingTotal = Money::add($customer->previous_due_amount, Money::sum($dueOrders->pluck('due_amount')));

                BatteryCustomerPaymentDetail::create([
                    'customer_id' => $customer->id,
                    'paid_amount' => $usedPaid,
                    'discount_amount' => $usedDisc,
                    'previous_due_amount' => $applied,
                    'due_amount' => $remainingTotal,
                    'due_collection_date' => $date,
                    'payment_method' => $method,
                    'transaction_id' => $trxId,
                    'payment_status' => Money::cmp($customer->previous_due_amount, 0) <= 0 ? 'Paid' : 'Partial Paid',
                    'user_id' => $userId,
                ]);
            }

            $ordersPaidTotal = '0.00';
            $ordersDiscTotal = '0.00';

            if (($collectionType === 'all' || $collectionType === 'invoice') && Money::cmp(Money::add($availPaid, $availDiscount), 0) > 0) {
                foreach ($dueOrders as $order) {
                    $remainingPower = Money::add($availPaid, $availDiscount);
                    if (Money::cmp($remainingPower, 0) <= 0) {
                        break;
                    }

                    $orderDue = Money::maxZero($order->due_amount ?? 0);
                    if (Money::cmp($orderDue, 0) <= 0) {
                        continue;
                    }

                    $toApply = Money::min($remainingPower, $orderDue);
                    $discRatio = Money::div($availDiscount, $remainingPower, 6);
                    $usedDisc = Money::min($availDiscount, Money::mul($toApply, $discRatio));
                    $usedPaid = Money::min(Money::sub($toApply, $usedDisc), $availPaid);
                    $gap = Money::sub($toApply, Money::add($usedPaid, $usedDisc));
                    if (Money::cmp($gap, 0) > 0) {
                        if (Money::cmp(Money::sub($availPaid, $usedPaid), $gap) >= 0) {
                            $usedPaid = Money::add($usedPaid, $gap);
                        } else {
                            $usedDisc = Money::add($usedDisc, $gap);
                        }
                    }

                    $availPaid = Money::maxZero(Money::sub($availPaid, $usedPaid));
                    $availDiscount = Money::maxZero(Money::sub($availDiscount, $usedDisc));

                    $appliedOnOrder = Money::add($usedPaid, $usedDisc);
                    $newOrderDue = Money::maxZero(Money::sub($orderDue, $appliedOnOrder));

                    $order->paid_amount = Money::add($order->paid_amount ?? 0, $usedPaid);
                    $order->due_amount = $newOrderDue;
                    $order->save();

                    BatteryOrderPaymentDetail::create([
                        'order_id' => $order->id,
                        'paid_amount' => $usedPaid,
                        'discount_amount' => $usedDisc,
                        'due_collection_date' => $date,
                        'payment_method' => $method,
                        'transaction_id' => $trxId,
                        'payment_status' => Money::cmp($newOrderDue, 0) <= 0 ? 'Paid' : 'Partial Paid',
                        'user_id' => $userId,
                    ]);

                    $ordersPaidTotal = Money::add($ordersPaidTotal, $usedPaid);
                    $ordersDiscTotal = Money::add($ordersDiscTotal, $usedDisc);
                    $appliedInvoices = Money::add($appliedInvoices, $appliedOnOrder);
                }

                if (Money::cmp(Money::add($ordersPaidTotal, $ordersDiscTotal), 0) > 0) {
                    $remOrdersDue = Money::sum(BatteryOrder::where('customer_id', $customer->id)->pluck('due_amount'));
                    $remTotal = Money::add($customer->previous_due_amount ?? 0, $remOrdersDue);

                    BatteryCustomerPaymentDetail::create([
                        'customer_id' => $customer->id,
                        'paid_amount' => $ordersPaidTotal,
                        'discount_amount' => $ordersDiscTotal,
                        'previous_due_amount' => '0.00',
                        'due_amount' => $remTotal,
                        'due_collection_date' => $date,
                        'payment_method' => $method,
                        'transaction_id' => $trxId,
                        'payment_status' => Money::cmp($remTotal, 0) <= 0 ? 'Paid' : 'Partial Paid',
                        'user_id' => $userId,
                    ]);
                }
            }

            $finalOrdersDue = Money::sum(BatteryOrder::where('customer_id', $customer->id)->pluck('due_amount'));
            $finalTotalDue = Money::add($customer->previous_due_amount ?? 0, $finalOrdersDue);

            ActivityLogger::log(
                'Payment',
                'Battery / Customer Due',
                "Collected Battery due for '{$customer->customer_name}' (Prev: ৳{$appliedPrev}, Invoices: ৳{$appliedInvoices}, Remaining: ৳{$finalTotalDue})",
                ['customer_id' => $customer->id],
            );

            return [
                'applied_previous' => $appliedPrev,
                'applied_invoices' => $appliedInvoices,
                'remaining_total_due' => $finalTotalDue,
            ];
        });
    }

    /**
     * Pay Battery supplier due via FIFO.
     * Step 1 (previous payable) writes ONLY to battery_supplier_due_collections;
     * Step 2 (purchase invoices) writes ONLY to battery_purchase_payment_details — preventing 2x double-counting!
     *
     * @return array{applied_previous: string, applied_purchases: string, remaining_total_due: string}
     */
    public function paySupplierDue(int $supplierId, array $data, int $userId): array
    {
        $availPaid = Money::maxZero($data['paid_amount'] ?? 0);
        $availDiscount = Money::maxZero($data['discount_amount'] ?? 0);
        $collectionType = in_array($data['collection_type'] ?? 'all', ['all', 'previous', 'purchase'], true)
            ? ($data['collection_type'] ?? 'all')
            : 'all';

        if (Money::cmp(Money::add($availPaid, $availDiscount), 0) <= 0) {
            throw new InvalidArgumentException('Payment or discount amount must be greater than zero.');
        }

        return DB::transaction(function () use ($supplierId, $availPaid, $availDiscount, $collectionType, $data, $userId): array {
            $supplier = BatterySupplier::lockForUpdate()->findOrFail($supplierId);
            $duePurchases = BatteryPurchase::where('supplier_id', $supplier->id)
                ->whereRaw('CAST(COALESCE(due_amount, 0) AS DECIMAL(15,2)) > 0')
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->sortBy(fn ($p) => [$p->date ?? $p->created_at?->toDateString() ?? '', $p->id])
                ->values();

            $date = Carbon::parse($data['date'] ?? now())->toDateString();
            $method = $data['payment_method'] ?? 'Cash';
            $trxId = $data['transaction_id'] ?? null;

            $appliedPrev = '0.00';
            $appliedPurchases = '0.00';

            $supplierPrevDue = Money::maxZero($supplier->purchase_payable_amount ?? 0);
            if (($collectionType === 'all' || $collectionType === 'previous') && Money::cmp($supplierPrevDue, 0) > 0) {
                $power = Money::add($availPaid, $availDiscount);
                if (Money::cmp($power, $supplierPrevDue) >= 0) {
                    $usedDisc = Money::min($availDiscount, $supplierPrevDue);
                    $usedPaid = Money::sub($supplierPrevDue, $usedDisc);
                    $applied = $supplierPrevDue;
                    $supplier->purchase_payable_amount = '0.00';
                    $availDiscount = Money::sub($availDiscount, $usedDisc);
                    $availPaid = Money::sub($availPaid, $usedPaid);
                } else {
                    $usedPaid = $availPaid;
                    $usedDisc = $availDiscount;
                    $applied = $power;
                    $supplier->purchase_payable_amount = Money::maxZero(Money::sub($supplierPrevDue, $applied));
                    $availPaid = '0.00';
                    $availDiscount = '0.00';
                }
                $supplier->save();
                $appliedPrev = $applied;

                $remainingTotal = Money::add($supplier->purchase_payable_amount, Money::sum($duePurchases->pluck('due_amount')));

                BatterySupplierDueCollection::create([
                    'supplier_id' => $supplier->id,
                    'paid_amount' => $usedPaid,
                    'discount_amount' => $usedDisc,
                    'due_amount' => $remainingTotal,
                    'payment_date' => $date,
                    'payment_method' => $method,
                    'transaction_id' => $trxId,
                    'user_id' => $userId,
                ]);
            }

            if (($collectionType === 'all' || $collectionType === 'purchase') && Money::cmp(Money::add($availPaid, $availDiscount), 0) > 0) {
                foreach ($duePurchases as $purchase) {
                    $remainingPower = Money::add($availPaid, $availDiscount);
                    if (Money::cmp($remainingPower, 0) <= 0) {
                        break;
                    }

                    $purchaseDue = Money::maxZero($purchase->due_amount ?? 0);
                    if (Money::cmp($purchaseDue, 0) <= 0) {
                        continue;
                    }

                    $toApply = Money::min($remainingPower, $purchaseDue);
                    $discRatio = Money::div($availDiscount, $remainingPower, 6);
                    $usedDisc = Money::min($availDiscount, Money::mul($toApply, $discRatio));
                    $usedPaid = Money::min(Money::sub($toApply, $usedDisc), $availPaid);
                    $gap = Money::sub($toApply, Money::add($usedPaid, $usedDisc));
                    if (Money::cmp($gap, 0) > 0) {
                        if (Money::cmp(Money::sub($availPaid, $usedPaid), $gap) >= 0) {
                            $usedPaid = Money::add($usedPaid, $gap);
                        } else {
                            $usedDisc = Money::add($usedDisc, $gap);
                        }
                    }

                    $availPaid = Money::maxZero(Money::sub($availPaid, $usedPaid));
                    $availDiscount = Money::maxZero(Money::sub($availDiscount, $usedDisc));

                    $appliedOnPurchase = Money::add($usedPaid, $usedDisc);
                    $newDue = Money::maxZero(Money::sub($purchaseDue, $appliedOnPurchase));

                    $purchase->paid_amount = Money::add($purchase->paid_amount ?? 0, $usedPaid);
                    $purchase->due_amount = $newDue;
                    $purchase->purchase_payable_amount = $newDue;
                    if (Money::cmp($usedDisc, 0) > 0) {
                        $purchase->discount_amount = Money::add($purchase->discount_amount ?? 0, $usedDisc);
                    }
                    $purchase->save();

                    BatteryPurchasePaymentDetail::create([
                        'purchases_id' => $purchase->id,
                        'paid_amount' => $usedPaid,
                        'discount_amount' => $usedDisc,
                        'purchase_due_collection_date' => $date,
                        'payment_method' => $method,
                        'transaction_id' => $trxId,
                        'payment_status' => Money::cmp($newDue, 0) <= 0 ? 'Fully Paid' : 'Partial Paid',
                        'user_id' => $userId,
                    ]);

                    $appliedPurchases = Money::add($appliedPurchases, $appliedOnPurchase);
                }
            }

            $finalPurchasesDue = Money::sum(BatteryPurchase::where('supplier_id', $supplier->id)->pluck('due_amount'));
            $finalTotalDue = Money::add($supplier->purchase_payable_amount ?? 0, $finalPurchasesDue);

            ActivityLogger::log(
                'Payment',
                'Battery / Supplier Due',
                "Paid Battery supplier '{$supplier->name}' (Prev: ৳{$appliedPrev}, Purchases: ৳{$appliedPurchases}, Remaining: ৳{$finalTotalDue})",
                ['supplier_id' => $supplier->id],
            );

            return [
                'applied_previous' => $appliedPrev,
                'applied_purchases' => $appliedPurchases,
                'remaining_total_due' => $finalTotalDue,
            ];
        });
    }

    /**
     * @return array{previous_due: string, orders_due: string, total_due: string, total_billed: string, total_paid: string, total_returns: string}
     */
    public function customerBalance(int $customerId): array
    {
        $customer = BatteryCustomer::findOrFail($customerId);
        $orders = BatteryOrder::where('customer_id', $customer->id)->get();

        $prevDue = Money::maxZero($customer->previous_due_amount ?? 0);
        $ordersDue = Money::sum($orders->pluck('due_amount'));
        $totalBilled = Money::sum($orders->map(fn ($o) => Money::maxZero(Money::add(Money::sub($o->sub_total ?? 0, $o->discount_amount ?? 0), $o->delivery_charge ?? 0))));

        $orderPayments = Money::sum(
            BatteryOrderPaymentDetail::whereIn('order_id', $orders->pluck('id'))->pluck('paid_amount')
        );
        $prevDuePayments = Money::sum(
            BatteryCustomerPaymentDetail::where('customer_id', $customer->id)
                ->whereRaw('CAST(COALESCE(previous_due_amount, 0) AS DECIMAL(15,2)) > 0')
                ->pluck('paid_amount')
        );
        $totalReturns = Money::sum($customer->productReturns()->pluck('amount'));

        return [
            'previous_due' => $prevDue,
            'orders_due' => $ordersDue,
            'total_due' => Money::add($prevDue, $ordersDue),
            'total_billed' => $totalBilled,
            'total_paid' => Money::add($orderPayments, $prevDuePayments),
            'total_returns' => $totalReturns,
        ];
    }

    /**
     * @return array{previous_due: string, purchases_due: string, total_due: string, total_purchased: string, total_paid: string, total_returns: string}
     */
    public function supplierBalance(int $supplierId): array
    {
        $supplier = BatterySupplier::findOrFail($supplierId);
        $purchases = BatteryPurchase::where('supplier_id', $supplier->id)->get();

        $prevDue = Money::maxZero($supplier->purchase_payable_amount ?? 0);
        $purchasesDue = Money::sum($purchases->pluck('due_amount'));
        $totalPurchased = Money::sum($purchases->map(fn ($p) => Money::maxZero(Money::add(Money::sub($p->grand_subtotal ?? 0, $p->discount_amount ?? 0), $p->delivery_charge ?? 0))));

        $purchasePayments = Money::sum(
            BatteryPurchasePaymentDetail::whereIn('purchases_id', $purchases->pluck('id'))->pluck('paid_amount')
        );
        $prevDuePayments = Money::sum(
            BatterySupplierDueCollection::where('supplier_id', $supplier->id)->pluck('paid_amount')
        );
        $totalReturns = Money::sum($supplier->purchaseReturns()->pluck('amount'));

        return [
            'previous_due' => $prevDue,
            'purchases_due' => $purchasesDue,
            'total_due' => Money::add($prevDue, $purchasesDue),
            'total_purchased' => $totalPurchased,
            'total_paid' => Money::add($purchasePayments, $prevDuePayments),
            'total_returns' => $totalReturns,
        ];
    }
}
