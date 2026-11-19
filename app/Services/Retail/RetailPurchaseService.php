<?php

namespace App\Services\Retail;

use App\Models\Retail\RetailProduct;
use App\Models\Retail\RetailPurchase;
use App\Models\Retail\RetailPurchaseOrderDetail;
use App\Models\Retail\RetailPurchasePaymentDetail;
use App\Models\Retail\RetailPurchaseReturn;
use App\Models\Retail\RetailSupplier;
use App\Services\Shared\ActivityLogger;
use App\Services\Shared\SafeImageUploadService;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RetailPurchaseService
{
    public function __construct(
        private readonly RetailStockService $stockService,
        private readonly SafeImageUploadService $uploadService,
    ) {}

    /**
     * Create an atomic Retail purchase, recomputing every total server-side via bcmath.
     *
     * @param  array{supplier_id: int, date: string, referance_no?: ?string, delivery_charge?: mixed, discount_amount?: mixed, discount_type?: ?string, discount_rate?: mixed, paid_amount?: mixed, payment_method?: ?string, transaction_id?: ?string, attach_document?: ?string, items: array<int, array{product_id: int, quantity: int, cost_price: mixed}>}  $data
     */
    public function create(array $data, int $userId): RetailPurchase
    {
        $items = array_values($data['items'] ?? []);
        if (count($items) === 0) {
            throw new InvalidArgumentException('At least one product item is required for a purchase.');
        }

        return DB::transaction(function () use ($data, $items, $userId): RetailPurchase {
            RetailSupplier::lockForUpdate()->findOrFail($data['supplier_id']);

            $productIds = collect($items)->pluck('product_id')->map(fn ($id) => (int) $id)->unique()->sort()->values()->all();
            $products = RetailProduct::whereIn('id', $productIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            $grandSubtotal = '0.00';
            $normalizedItems = [];

            foreach ($items as $item) {
                $pid = (int) $item['product_id'];
                $qty = (int) $item['quantity'];
                $cost = Money::round($item['cost_price']);

                if (! $products->has($pid)) {
                    throw new InvalidArgumentException("Retail product #{$pid} not found.");
                }
                if ($qty <= 0) {
                    throw new InvalidArgumentException('Purchase item quantity must be greater than zero.');
                }
                if (Money::cmp($cost, 0) < 0) {
                    throw new InvalidArgumentException('Cost price cannot be negative.');
                }

                $lineSubtotal = Money::mul($cost, $qty);
                $grandSubtotal = Money::add($grandSubtotal, $lineSubtotal);
                $normalizedItems[] = [
                    'product' => $products->get($pid),
                    'quantity' => $qty,
                    'cost_price' => $cost,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $deliveryCharge = Money::maxZero($data['delivery_charge'] ?? 0);
            $discountType = ($data['discount_type'] ?? 'fixed') === 'percent' ? 'percent' : 'fixed';
            $discountRate = $discountType === 'percent' ? Money::maxZero($data['discount_rate'] ?? 0) : null;
            $discountAmount = $discountType === 'percent'
                ? Money::min(Money::percent($grandSubtotal, $discountRate), $grandSubtotal)
                : Money::min(Money::maxZero($data['discount_amount'] ?? 0), $grandSubtotal);

            $netPayable = Money::maxZero(Money::add(Money::sub($grandSubtotal, $discountAmount), $deliveryCharge));
            $paidAmount = Money::min(Money::maxZero($data['paid_amount'] ?? 0), $netPayable);
            $dueAmount = Money::maxZero(Money::sub($netPayable, $paidAmount));

            $date = Carbon::parse($data['date'])->toDateString();
            $lastId = (int) (RetailPurchase::lockForUpdate()->max('id') ?? 0);
            $purchaseCode = '#PurID'.str_pad((string) ($lastId + 1), 5, '0', STR_PAD_LEFT);

            $purchase = RetailPurchase::create([
                'purchase_id' => $purchaseCode,
                'referance_no' => $data['referance_no'] ?? null,
                'grand_subtotal' => $grandSubtotal,
                'delivery_charge' => $deliveryCharge,
                'discount_amount' => $discountAmount,
                'discount_type' => $discountType,
                'discount_rate' => $discountRate,
                'return_adjustment_amount' => '0.00',
                'purchase_payable_amount' => $dueAmount,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'date' => $date,
                'attach_document' => $data['attach_document'] ?? null,
                'supplier_id' => (int) $data['supplier_id'],
                'user_id' => $userId,
            ]);

            foreach ($normalizedItems as $line) {
                RetailPurchaseOrderDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $line['product']->id,
                    'quantity' => (string) $line['quantity'],
                    'return_quantity' => 0,
                    'cost_price' => $line['cost_price'],
                    'subtotal' => $line['subtotal'],
                    'user_id' => $userId,
                ]);

                $this->stockService->increaseForPurchase($line['product'], $line['quantity'], $line['cost_price']);
            }

            if (Money::cmp($paidAmount, 0) > 0) {
                RetailPurchasePaymentDetail::create([
                    'purchases_id' => $purchase->id,
                    'paid_amount' => $paidAmount,
                    'discount_amount' => $discountAmount,
                    'payment_method' => $data['payment_method'] ?? 'Cash',
                    'transaction_id' => $data['transaction_id'] ?? null,
                    'payment_status' => Money::cmp($dueAmount, 0) <= 0 ? 'Fully Paid' : 'Partial Paid',
                    'purchase_due_collection_date' => $date,
                    'user_id' => $userId,
                ]);
            }

            ActivityLogger::log(
                'Purchase',
                'Retail / Purchase',
                "Created Retail purchase {$purchase->purchase_id} (Total: {$netPayable}, Paid: {$paidAmount}, Due: {$dueAmount})",
                ['purchase_id' => $purchase->id, 'purchase_code' => $purchase->purchase_id],
            );

            return $purchase->fresh(['supplier', 'orderDetails.product', 'paymentDetails']);
        });
    }

    /**
     * Update an existing Retail purchase with full row locks, return guards, and WAC reconciliation.
     */
    public function update(int $purchaseId, array $data, int $userId): RetailPurchase
    {
        $items = array_values($data['items'] ?? []);
        if (count($items) === 0) {
            throw new InvalidArgumentException('Purchase must contain at least one product.');
        }

        return DB::transaction(function () use ($purchaseId, $data, $items, $userId): RetailPurchase {
            $purchase = RetailPurchase::lockForUpdate()->findOrFail($purchaseId);
            $oldDetails = RetailPurchaseOrderDetail::where('purchase_id', $purchase->id)->orderBy('id')->lockForUpdate()->get();
            $payments = RetailPurchasePaymentDetail::where('purchases_id', $purchase->id)->orderBy('id')->lockForUpdate()->get();

            $allProductIds = $oldDetails->pluck('product_id')
                ->merge(collect($items)->pluck('product_id'))
                ->map(fn ($id) => (int) $id)->unique()->sort()->values()->all();
            $products = RetailProduct::whereIn('id', $allProductIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            // Aggregate old quantities/costs by product_id
            $oldByProduct = [];
            foreach ($oldDetails as $od) {
                $pid = (int) $od->product_id;
                $q = (int) round((float) $od->quantity);
                $ret = (int) ($od->return_quantity ?? 0);
                if (! isset($oldByProduct[$pid])) {
                    $oldByProduct[$pid] = ['qty' => 0, 'cost' => Money::round($od->cost_price), 'returned' => 0];
                }
                $oldByProduct[$pid]['qty'] += $q;
                $oldByProduct[$pid]['returned'] += $ret;
            }

            // Aggregate new items
            $newByProduct = [];
            $grandSubtotal = '0.00';
            $normalizedItems = [];
            foreach ($items as $item) {
                $pid = (int) $item['product_id'];
                $qty = (int) $item['quantity'];
                $cost = Money::round($item['cost_price']);
                if (! $products->has($pid) || $qty <= 0 || Money::cmp($cost, 0) < 0) {
                    throw new InvalidArgumentException('Invalid purchase item data.');
                }
                $sub = Money::mul($cost, $qty);
                $grandSubtotal = Money::add($grandSubtotal, $sub);
                $newByProduct[$pid] = [
                    'qty' => ($newByProduct[$pid]['qty'] ?? 0) + $qty,
                    'cost' => $cost,
                ];
                $normalizedItems[] = [
                    'product_id' => $pid,
                    'quantity' => $qty,
                    'cost_price' => $cost,
                    'subtotal' => $sub,
                ];
            }

            // Enforce return guard: cannot drop or reduce below returned quantity
            foreach ($oldByProduct as $pid => $oldMeta) {
                $returned = max(
                    $oldMeta['returned'],
                    (int) RetailPurchaseReturn::where('purchase_id', $purchase->id)->where('product_id', $pid)->sum('quantity'),
                );
                $proposedQty = $newByProduct[$pid]['qty'] ?? 0;
                if ($proposedQty < $returned) {
                    $pName = $products->get($pid)?->product_name ?? "#{$pid}";
                    throw new InvalidArgumentException("Cannot reduce '{$pName}' below already returned quantity ({$returned}).");
                }
            }

            // Apply stock & WAC delta per product
            foreach ($allProductIds as $pid) {
                $product = $products->get($pid);
                $oldQty = $oldByProduct[$pid]['qty'] ?? 0;
                $oldCost = $oldByProduct[$pid]['cost'] ?? '0.00';
                $newQty = $newByProduct[$pid]['qty'] ?? 0;
                $newCost = $newByProduct[$pid]['cost'] ?? $oldCost;
                if ($oldQty !== $newQty || Money::cmp($oldCost, $newCost) !== 0) {
                    $this->stockService->applyPurchaseDelta($product, $oldQty, $oldCost, $newQty, $newCost);
                }
            }

            // Preserve return_quantity per product on detail recreation
            $returnedMap = collect($oldByProduct)->map(fn ($m) => $m['returned'])->all();
            RetailPurchaseOrderDetail::where('purchase_id', $purchase->id)->delete();
            foreach ($normalizedItems as $line) {
                $retQty = $returnedMap[$line['product_id']] ?? 0;
                $returnedMap[$line['product_id']] = 0;
                RetailPurchaseOrderDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $line['product_id'],
                    'quantity' => (string) $line['quantity'],
                    'return_quantity' => $retQty,
                    'cost_price' => $line['cost_price'],
                    'subtotal' => $line['subtotal'],
                    'user_id' => $userId,
                ]);
            }

            $deliveryCharge = Money::maxZero($data['delivery_charge'] ?? $purchase->delivery_charge ?? 0);
            $discountType = ($data['discount_type'] ?? $purchase->discount_type ?? 'fixed') === 'percent' ? 'percent' : 'fixed';
            $discountRate = $discountType === 'percent' ? Money::maxZero($data['discount_rate'] ?? 0) : null;
            $discountAmount = $discountType === 'percent'
                ? Money::min(Money::percent($grandSubtotal, $discountRate), $grandSubtotal)
                : Money::min(Money::maxZero($data['discount_amount'] ?? 0), $grandSubtotal);

            $returnAdj = Money::maxZero($purchase->return_adjustment_amount ?? 0);
            $netPayable = Money::maxZero(Money::sub(Money::add(Money::sub($grandSubtotal, $discountAmount), $deliveryCharge), $returnAdj));

            $existingLedgerPaid = Money::sum($payments->pluck('paid_amount'));
            $requestedPaid = isset($data['paid_amount']) ? Money::maxZero($data['paid_amount']) : $existingLedgerPaid;
            $date = Carbon::parse($data['date'] ?? $purchase->date)->toDateString();

            if (Money::cmp($requestedPaid, $existingLedgerPaid) !== 0) {
                if ($payments->count() > 1) {
                    throw new InvalidArgumentException('Cannot directly overwrite paid amount when multiple payment installments exist.');
                }
                if ($payments->count() === 1) {
                    $first = $payments->first();
                    $first->paid_amount = Money::min($requestedPaid, $netPayable);
                    $first->discount_amount = $discountAmount;
                    $first->purchase_due_collection_date = $date;
                    $first->payment_method = $data['payment_method'] ?? $first->payment_method;
                    $first->save();
                } elseif (Money::cmp($requestedPaid, 0) > 0) {
                    RetailPurchasePaymentDetail::create([
                        'purchases_id' => $purchase->id,
                        'paid_amount' => Money::min($requestedPaid, $netPayable),
                        'discount_amount' => $discountAmount,
                        'payment_method' => $data['payment_method'] ?? 'Cash',
                        'payment_status' => Money::cmp($requestedPaid, $netPayable) >= 0 ? 'Fully Paid' : 'Partial Paid',
                        'purchase_due_collection_date' => $date,
                        'user_id' => $userId,
                    ]);
                }
            }

            $authoritativePaid = Money::sum(
                RetailPurchasePaymentDetail::where('purchases_id', $purchase->id)->pluck('paid_amount')
            );
            $newDue = Money::maxZero(Money::sub($netPayable, $authoritativePaid));

            $purchase->update([
                'supplier_id' => (int) ($data['supplier_id'] ?? $purchase->supplier_id),
                'referance_no' => $data['referance_no'] ?? $purchase->referance_no,
                'date' => $date,
                'grand_subtotal' => $grandSubtotal,
                'delivery_charge' => $deliveryCharge,
                'discount_amount' => $discountAmount,
                'discount_type' => $discountType,
                'discount_rate' => $discountRate,
                'paid_amount' => $authoritativePaid,
                'due_amount' => $newDue,
                'purchase_payable_amount' => $newDue,
            ]);

            ActivityLogger::log(
                'Update',
                'Retail / Purchase',
                "Updated Retail purchase {$purchase->purchase_id}",
                ['purchase_id' => $purchase->id],
            );

            return $purchase->fresh(['supplier', 'orderDetails.product', 'paymentDetails']);
        });
    }

    /**
     * Record a direct payment against a Retail purchase (accounts for return_adjustment_amount).
     */
    public function recordPayment(int $purchaseId, array $data, int $userId): RetailPurchasePaymentDetail
    {
        $paidAmount = Money::maxZero($data['paid_amount'] ?? 0);
        $discountAmount = Money::maxZero($data['discount_amount'] ?? 0);

        if (Money::cmp(Money::add($paidAmount, $discountAmount), 0) <= 0) {
            throw new InvalidArgumentException('Payment or discount amount must be greater than zero.');
        }

        return DB::transaction(function () use ($purchaseId, $paidAmount, $discountAmount, $data, $userId): RetailPurchasePaymentDetail {
            $purchase = RetailPurchase::lockForUpdate()->findOrFail($purchaseId);
            $existingPaid = Money::sum(
                RetailPurchasePaymentDetail::where('purchases_id', $purchase->id)->lockForUpdate()->pluck('paid_amount')
            );
            $existingPayDiscounts = Money::sum(
                RetailPurchasePaymentDetail::where('purchases_id', $purchase->id)->pluck('discount_amount')
            );

            $netTotal = $this->effectiveNetPayable($purchase);
            $currentDue = Money::maxZero(Money::sub($netTotal, $existingPaid));

            if (Money::cmp($currentDue, 0) <= 0) {
                throw new InvalidArgumentException("Purchase {$purchase->purchase_id} is already fully paid.");
            }

            $appliedDiscount = Money::min($discountAmount, $currentDue);
            $remainingAfterDisc = Money::sub($currentDue, $appliedDiscount);
            $appliedPaid = Money::min($paidAmount, $remainingAfterDisc);
            $newDue = Money::maxZero(Money::sub($remainingAfterDisc, $appliedPaid));
            $newTotalPaid = Money::add($existingPaid, $appliedPaid);
            $status = Money::cmp($newDue, 0) <= 0 ? 'Fully Paid' : 'Partial Paid';

            $payment = RetailPurchasePaymentDetail::create([
                'purchases_id' => $purchase->id,
                'paid_amount' => $appliedPaid,
                'discount_amount' => $appliedDiscount,
                'payment_method' => $data['payment_method'] ?? 'Cash',
                'transaction_id' => $data['transaction_id'] ?? null,
                'payment_status' => $status,
                'purchase_due_collection_date' => Carbon::parse($data['date'] ?? now())->toDateString(),
                'user_id' => $userId,
            ]);

            $purchase->paid_amount = $newTotalPaid;
            $purchase->due_amount = $newDue;
            $purchase->purchase_payable_amount = $newDue;
            if (Money::cmp($appliedDiscount, 0) > 0) {
                $purchase->discount_amount = Money::add($purchase->discount_amount ?? 0, $appliedDiscount);
            }
            $purchase->save();

            ActivityLogger::log(
                'Payment',
                'Retail / Purchase',
                "Recorded payment of ৳{$appliedPaid} (discount ৳{$appliedDiscount}) for Retail purchase {$purchase->purchase_id}",
                ['purchase_id' => $purchase->id, 'payment_id' => $payment->id],
            );

            return $payment;
        });
    }

    /**
     * Delete a Retail purchase safely (blocks if returns or multiple payments exist).
     */
    public function delete(int $purchaseId): void
    {
        DB::transaction(function () use ($purchaseId): void {
            $purchase = RetailPurchase::lockForUpdate()->findOrFail($purchaseId);

            if (RetailPurchaseReturn::where('purchase_id', $purchase->id)->exists()) {
                throw new InvalidArgumentException("Cannot delete purchase {$purchase->purchase_id} because purchase returns exist.");
            }

            $payments = RetailPurchasePaymentDetail::where('purchases_id', $purchase->id)->lockForUpdate()->get();
            if ($payments->count() > 1) {
                throw new InvalidArgumentException("Cannot delete purchase {$purchase->purchase_id} with multiple payment installments.");
            }

            $details = RetailPurchaseOrderDetail::where('purchase_id', $purchase->id)->orderBy('id')->lockForUpdate()->get();
            $productIds = $details->pluck('product_id')->map(fn ($id) => (int) $id)->unique()->sort()->values()->all();
            $products = RetailProduct::whereIn('id', $productIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            foreach ($details as $detail) {
                $product = $products->get((int) $detail->product_id);
                if ($product) {
                    $netQty = max(0, (int) round((float) $detail->quantity) - (int) ($detail->return_quantity ?? 0));
                    $this->stockService->reverseForPurchaseDelete($product, $netQty, Money::round($detail->cost_price), $purchase->id);
                }
            }

            $purchaseCode = $purchase->purchase_id;
            $attachment = $purchase->attach_document;

            RetailPurchasePaymentDetail::where('purchases_id', $purchase->id)->delete();
            RetailPurchaseOrderDetail::where('purchase_id', $purchase->id)->delete();
            $purchase->delete();

            if ($attachment) {
                $this->uploadService->delete($attachment);
            }

            ActivityLogger::log(
                'Delete',
                'Retail / Purchase',
                "Deleted Retail purchase {$purchaseCode}",
                ['purchase_id' => $purchaseId, 'purchase_code' => $purchaseCode],
            );
        });
    }

    /**
     * Split selected line items from a source purchase into a brand-new purchase invoice
     * (zero stock movement, clean multi-payment ledger reconciliation).
     *
     * @param  list<int>  $detailIds
     */
    public function splitInvoice(int $sourcePurchaseId, array $detailIds, array $options, int $userId): RetailPurchase
    {
        $detailIds = array_values(array_unique(array_map('intval', $detailIds)));
        if (count($detailIds) === 0) {
            throw new InvalidArgumentException('Select at least one item to split.');
        }

        return DB::transaction(function () use ($sourcePurchaseId, $detailIds, $options, $userId): RetailPurchase {
            $source = RetailPurchase::lockForUpdate()->findOrFail($sourcePurchaseId);
            $allDetails = RetailPurchaseOrderDetail::where('purchase_id', $source->id)->orderBy('id')->lockForUpdate()->get();

            $selected = $allDetails->whereIn('id', $detailIds);
            $remaining = $allDetails->whereNotIn('id', $detailIds);

            if ($selected->isEmpty() || $remaining->isEmpty()) {
                throw new InvalidArgumentException('Split must move at least one item and leave at least one item on the original purchase.');
            }

            if ($selected->contains(fn ($d) => (int) ($d->return_quantity ?? 0) > 0)) {
                throw new InvalidArgumentException('Cannot split line items that have already been returned.');
            }

            $newSubtotal = Money::sum($selected->map(fn ($d) => Money::mul($d->cost_price, $d->quantity)));
            $remSubtotal = Money::sum($remaining->map(fn ($d) => Money::mul($d->cost_price, $d->quantity)));

            $newDiscType = ($options['new_discount_type'] ?? 'fixed') === 'percent' ? 'percent' : 'fixed';
            $newDiscRate = $newDiscType === 'percent' ? Money::maxZero($options['new_discount_rate'] ?? 0) : null;
            $newDiscount = $newDiscType === 'percent'
                ? Money::min(Money::percent($newSubtotal, $newDiscRate), $newSubtotal)
                : Money::min(Money::maxZero($options['new_discount_amount'] ?? 0), $newSubtotal);
            $newNetPayable = Money::maxZero(Money::sub($newSubtotal, $newDiscount));

            $sourceTotalBefore = Money::round($source->grand_subtotal ?? 0);
            $remDiscType = ($options['original_discount_type'] ?? $source->discount_type ?? 'fixed') === 'percent' ? 'percent' : 'fixed';
            if (isset($options['original_discount_amount']) || isset($options['original_discount_rate'])) {
                $remDiscRate = $remDiscType === 'percent' ? Money::maxZero($options['original_discount_rate'] ?? 0) : null;
                $remDiscount = $remDiscType === 'percent'
                    ? Money::min(Money::percent($remSubtotal, $remDiscRate), $remSubtotal)
                    : Money::min(Money::maxZero($options['original_discount_amount'] ?? 0), $remSubtotal);
            } elseif ($remDiscType === 'percent' && Money::cmp($source->discount_rate ?? 0, 0) > 0) {
                $remDiscRate = Money::round($source->discount_rate);
                $remDiscount = Money::min(Money::percent($remSubtotal, $remDiscRate), $remSubtotal);
            } else {
                $remDiscRate = null;
                $ratio = Money::cmp($sourceTotalBefore, 0) > 0 ? Money::div($remSubtotal, $sourceTotalBefore, 6) : '0';
                $remDiscount = Money::min(Money::mul($source->discount_amount ?? 0, $ratio), $remSubtotal);
            }

            $sourceDelivery = Money::maxZero($source->delivery_charge ?? 0);
            $sourceReturnAdj = Money::maxZero($source->return_adjustment_amount ?? 0);
            $remNetPayable = Money::maxZero(Money::sub(Money::add(Money::sub($remSubtotal, $remDiscount), $sourceDelivery), $sourceReturnAdj));

            $sourcePayments = RetailPurchasePaymentDetail::where('purchases_id', $source->id)->orderBy('id')->lockForUpdate()->get();
            $historicalPaid = $sourcePayments->isNotEmpty()
                ? Money::sum($sourcePayments->pluck('paid_amount'))
                : Money::maxZero($source->paid_amount ?? 0);

            $combinedPayable = Money::add($remNetPayable, $newNetPayable);
            if (Money::cmp($combinedPayable, 0) <= 0) {
                $newPaid = '0.00';
                $remPaid = '0.00';
            } elseif (Money::cmp($historicalPaid, $combinedPayable) >= 0) {
                $newPaid = $newNetPayable;
                $remPaid = Money::add($remNetPayable, Money::sub($historicalPaid, $combinedPayable));
            } else {
                $share = Money::div($newNetPayable, $combinedPayable, 6);
                $newPaid = Money::min(Money::mul($historicalPaid, $share), $newNetPayable);
                $remPaid = Money::maxZero(Money::sub($historicalPaid, $newPaid));
            }

            $newDue = Money::maxZero(Money::sub($newNetPayable, $newPaid));
            $remDue = Money::maxZero(Money::sub($remNetPayable, $remPaid));

            $lastId = (int) (RetailPurchase::lockForUpdate()->max('id') ?? 0);
            $newPurchaseCode = '#PurID'.str_pad((string) ($lastId + 1), 5, '0', STR_PAD_LEFT);
            $splitDate = Carbon::parse($options['date'] ?? $source->date ?? now())->toDateString();

            $newPurchase = RetailPurchase::create([
                'purchase_id' => $newPurchaseCode,
                'referance_no' => $options['referance_no'] ?? $source->referance_no,
                'supplier_id' => (int) ($options['supplier_id'] ?? $source->supplier_id),
                'date' => $splitDate,
                'grand_subtotal' => $newSubtotal,
                'delivery_charge' => '0.00',
                'discount_amount' => $newDiscount,
                'discount_type' => $newDiscType,
                'discount_rate' => $newDiscRate,
                'return_adjustment_amount' => '0.00',
                'paid_amount' => $newPaid,
                'due_amount' => $newDue,
                'purchase_payable_amount' => $newDue,
                'user_id' => $userId,
            ]);

            RetailPurchaseOrderDetail::whereIn('id', $selected->pluck('id')->all())
                ->update(['purchase_id' => $newPurchase->id]);

            if (Money::cmp($newPaid, 0) > 0) {
                RetailPurchasePaymentDetail::create([
                    'purchases_id' => $newPurchase->id,
                    'paid_amount' => $newPaid,
                    'discount_amount' => $newDiscount,
                    'payment_method' => $sourcePayments->first()?->payment_method ?? 'Cash',
                    'payment_status' => Money::cmp($newDue, 0) <= 0 ? 'Fully Paid' : 'Partial Paid',
                    'purchase_due_collection_date' => $splitDate,
                    'user_id' => $userId,
                ]);
            }

            // Reconcile source payment ledger safely across 1 or N installments (fixing legacy inflation bug)
            $remainingToKeep = $remPaid;
            foreach ($sourcePayments as $idx => $payRow) {
                if ($idx === $sourcePayments->count() - 1) {
                    $payRow->paid_amount = $remainingToKeep;
                    $payRow->payment_status = Money::cmp($remDue, 0) <= 0 ? 'Fully Paid' : 'Partial Paid';
                    $payRow->save();
                } else {
                    $keep = Money::min($payRow->paid_amount, $remainingToKeep);
                    $payRow->paid_amount = $keep;
                    $payRow->save();
                    $remainingToKeep = Money::maxZero(Money::sub($remainingToKeep, $keep));
                }
            }

            $source->update([
                'grand_subtotal' => $remSubtotal,
                'discount_amount' => $remDiscount,
                'discount_type' => $remDiscType,
                'discount_rate' => $remDiscRate,
                'paid_amount' => $remPaid,
                'due_amount' => $remDue,
                'purchase_payable_amount' => $remDue,
            ]);

            ActivityLogger::log(
                'Split',
                'Retail / Purchase',
                "Split Retail purchase {$source->purchase_id} -> created {$newPurchase->purchase_id}",
                ['source_purchase_id' => $source->id, 'new_purchase_id' => $newPurchase->id],
            );

            return $newPurchase->fresh(['supplier', 'orderDetails.product', 'paymentDetails']);
        });
    }

    public function effectiveNetPayable(RetailPurchase $purchase): string
    {
        $gross = Money::sub($purchase->grand_subtotal ?? 0, $purchase->discount_amount ?? 0);
        $withDelivery = Money::add($gross, $purchase->delivery_charge ?? 0);

        return Money::maxZero(Money::sub($withDelivery, $purchase->return_adjustment_amount ?? 0));
    }
}
