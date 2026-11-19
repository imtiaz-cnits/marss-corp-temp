<?php

namespace App\Services\Retail;

use App\Models\Retail\RetailCustomer;
use App\Models\Retail\RetailCustomerPaymentDetail;
use App\Models\Retail\RetailOrder;
use App\Models\Retail\RetailOrderPaymentDetail;
use App\Models\Retail\RetailPurchase;
use App\Models\Retail\RetailPurchasePaymentDetail;
use App\Models\Retail\RetailSupplier;
use App\Models\Retail\RetailSupplierDueCollection;
use App\Services\Shared\ActivityLogger;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RetailDueCollectionService
{
    /**
     * Collect customer due using strict FIFO across previous opening due and unpaid Retail invoices.
     * Recomputes dynamic Step-2 ratio from remaining cash/discount (fixing legacy proportional drift).
     *
     * @param  array{paid_amount: mixed, discount_amount?: mixed, payment_method?: ?string, transaction_id?: ?string, date?: ?string, collection_type?: ?string}  $data
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
            $customer = RetailCustomer::lockForUpdate()->findOrFail($customerId);
            $dueOrders = RetailOrder::where('customer_id', $customer->id)
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

            // Step 1: Previous Opening Due
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

                RetailCustomerPaymentDetail::create([
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

            // Step 2: FIFO across due invoices
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
                    // Dynamic ratio from CURRENT remaining paid/discount (prevents legacy Step-1 distortion)
                    $discRatio = Money::div($availDiscount, $remainingPower, 6);
                    $usedDisc = Money::min($availDiscount, Money::mul($toApply, $discRatio));
                    $usedPaid = Money::min(Money::sub($toApply, $usedDisc), $availPaid);
                    // Handle 1-poisha rounding remainder cleanly
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

                    RetailOrderPaymentDetail::create([
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
                    $remOrdersDue = Money::sum(RetailOrder::where('customer_id', $customer->id)->pluck('due_amount'));
                    $remTotal = Money::add($customer->previous_due_amount ?? 0, $remOrdersDue);

                    // Summary audit row with previous_due_amount = 0.00 (reports exclude previous_due_amount=0 to prevent 2x double-counting)
                    RetailCustomerPaymentDetail::create([
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

            $finalOrdersDue = Money::sum(RetailOrder::where('customer_id', $customer->id)->pluck('due_amount'));
            $finalTotalDue = Money::add($customer->previous_due_amount ?? 0, $finalOrdersDue);

            ActivityLogger::log(
                'Payment',
                'Retail / Customer Due',
                "Collected Retail due for '{$customer->customer_name}' (Prev: ৳{$appliedPrev}, Invoices: ৳{$appliedInvoices}, Remaining: ৳{$finalTotalDue})",
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
     * Collect/pay supplier due using strict FIFO across previous opening payable and unpaid Retail purchases.
     *
     * @param  array{paid_amount: mixed, discount_amount?: mixed, payment_method?: ?string, transaction_id?: ?string, date?: ?string, collection_type?: ?string}  $data
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
            $supplier = RetailSupplier::lockForUpdate()->findOrFail($supplierId);
            $duePurchases = RetailPurchase::where('supplier_id', $supplier->id)
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

            // Step 1: Previous Opening Payable
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

                RetailSupplierDueCollection::create([
                    'supplier_id' => $supplier->id,
                    'paid_amount' => $usedPaid,
                    'discount_amount' => $usedDisc,
                    'purchase_payable_amount' => $applied,
                    'due_amount' => $remainingTotal,
                    'due_collection_date' => $date,
                    'payment_method' => $method,
                    'transaction_id' => $trxId,
                    'payment_status' => Money::cmp($supplier->purchase_payable_amount, 0) <= 0 ? 'Paid' : 'Partial Paid',
                    'user_id' => $userId,
                ]);
            }

            // Step 2: FIFO across due purchases
            $purchasesPaidTotal = '0.00';
            $purchasesDiscTotal = '0.00';

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

                    RetailPurchasePaymentDetail::create([
                        'purchases_id' => $purchase->id,
                        'paid_amount' => $usedPaid,
                        'discount_amount' => $usedDisc,
                        'purchase_due_collection_date' => $date,
                        'payment_method' => $method,
                        'transaction_id' => $trxId,
                        'payment_status' => Money::cmp($newDue, 0) <= 0 ? 'Fully Paid' : 'Partial Paid',
                        'user_id' => $userId,
                    ]);

                    $purchasesPaidTotal = Money::add($purchasesPaidTotal, $usedPaid);
                    $purchasesDiscTotal = Money::add($purchasesDiscTotal, $usedDisc);
                    $appliedPurchases = Money::add($appliedPurchases, $appliedOnPurchase);
                }

                if (Money::cmp(Money::add($purchasesPaidTotal, $purchasesDiscTotal), 0) > 0) {
                    $remPurchasesDue = Money::sum(RetailPurchase::where('supplier_id', $supplier->id)->pluck('due_amount'));
                    $remTotal = Money::add($supplier->purchase_payable_amount ?? 0, $remPurchasesDue);

                    RetailSupplierDueCollection::create([
                        'supplier_id' => $supplier->id,
                        'paid_amount' => $purchasesPaidTotal,
                        'discount_amount' => $purchasesDiscTotal,
                        'purchase_payable_amount' => '0.00',
                        'due_amount' => $remTotal,
                        'due_collection_date' => $date,
                        'payment_method' => $method,
                        'transaction_id' => $trxId,
                        'payment_status' => Money::cmp($remTotal, 0) <= 0 ? 'Paid' : 'Partial Paid',
                        'user_id' => $userId,
                    ]);
                }
            }

            $finalPurchasesDue = Money::sum(RetailPurchase::where('supplier_id', $supplier->id)->pluck('due_amount'));
            $finalTotalDue = Money::add($supplier->purchase_payable_amount ?? 0, $finalPurchasesDue);

            ActivityLogger::log(
                'Payment',
                'Retail / Supplier Due',
                "Paid Retail supplier '{$supplier->name}' (Prev: ৳{$appliedPrev}, Purchases: ৳{$appliedPurchases}, Remaining: ৳{$finalTotalDue})",
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
     * Authoritative Retail customer balance summary (fixes profile missing 2nd+ direct payments).
     *
     * @return array{previous_due: string, orders_due: string, total_due: string, total_billed: string, total_paid: string, total_returns: string}
     */
    public function customerBalance(int $customerId): array
    {
        $customer = RetailCustomer::findOrFail($customerId);
        $orders = RetailOrder::where('customer_id', $customer->id)->get();

        $prevDue = Money::maxZero($customer->previous_due_amount ?? 0);
        $ordersDue = Money::sum($orders->pluck('due_amount'));
        $totalBilled = Money::sum($orders->map(fn ($o) => Money::maxZero(Money::add(Money::sub($o->sub_total ?? 0, $o->discount_amount ?? 0), $o->delivery_charge ?? 0))));

        $orderPayments = Money::sum(
            RetailOrderPaymentDetail::whereIn('order_id', $orders->pluck('id'))->pluck('paid_amount')
        );
        $prevDuePayments = Money::sum(
            RetailCustomerPaymentDetail::where('customer_id', $customer->id)
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
     * Authoritative Retail supplier balance summary (fixes profile missing 2nd+ direct payments).
     *
     * @return array{previous_due: string, purchases_due: string, total_due: string, total_purchased: string, total_paid: string, total_returns: string}
     */
    public function supplierBalance(int $supplierId): array
    {
        $supplier = RetailSupplier::findOrFail($supplierId);
        $purchases = RetailPurchase::where('supplier_id', $supplier->id)->get();

        $prevDue = Money::maxZero($supplier->purchase_payable_amount ?? 0);
        $purchasesDue = Money::sum($purchases->pluck('due_amount'));
        $totalPurchased = Money::sum($purchases->map(fn ($p) => Money::maxZero(Money::add(Money::sub($p->grand_subtotal ?? 0, $p->discount_amount ?? 0), $p->delivery_charge ?? 0))));

        $purchasePayments = Money::sum(
            RetailPurchasePaymentDetail::whereIn('purchases_id', $purchases->pluck('id'))->pluck('paid_amount')
        );
        $prevDuePayments = Money::sum(
            RetailSupplierDueCollection::where('supplier_id', $supplier->id)
                ->whereRaw('CAST(COALESCE(purchase_payable_amount, 0) AS DECIMAL(15,2)) > 0')
                ->pluck('paid_amount')
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
