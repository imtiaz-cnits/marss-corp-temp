<?php

namespace App\Services\Battery;

use App\Models\Battery\BatteryCustomer;
use App\Models\Battery\BatteryOrder;
use App\Models\Battery\BatteryOrderDetail;
use App\Models\Battery\BatteryOrderPaymentDetail;
use App\Models\Battery\BatteryProduct;
use App\Models\Battery\BatteryProductReturn;
use App\Services\Shared\ActivityLogger;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * 100% Isolated Battery Sales & Invoice Service.
 * Fixes FIN-08 (fatal Schema crash), FIN-13 (full-edit wipes), and missing stock locks.
 */
class BatterySalesService
{
    public function __construct(
        private readonly BatteryStockService $stockService,
    ) {}

    public function createOrder(array $data, int $userId): BatteryOrder
    {
        $items = array_values($data['items'] ?? []);
        if (count($items) === 0) {
            throw new InvalidArgumentException('Battery POS cart cannot be empty.');
        }

        return DB::transaction(function () use ($data, $items, $userId): BatteryOrder {
            $customer = BatteryCustomer::lockForUpdate()->findOrFail($data['customer_id']);

            $productIds = collect($items)->pluck('product_id')->map(fn ($id) => (int) $id)->unique()->sort()->values()->all();
            $products = BatteryProduct::whereIn('id', $productIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            $neededByProduct = [];
            foreach ($items as $item) {
                $pid = (int) $item['product_id'];
                $qty = (int) $item['quantity'];
                if ($qty <= 0 || ! $products->has($pid)) {
                    throw new InvalidArgumentException('Invalid battery POS cart item.');
                }
                $neededByProduct[$pid] = ($neededByProduct[$pid] ?? 0) + $qty;
            }

            foreach ($neededByProduct as $pid => $needed) {
                $product = $products->get($pid);
                $avail = (int) round((float) ($product->quantity ?? 0));
                if ($avail < $needed) {
                    throw new InvalidArgumentException("Insufficient battery stock for '{$product->product_name}'. Available: {$avail}, Requested: {$needed}.");
                }
            }

            $subTotal = '0.00';
            $normalizedItems = [];
            foreach ($items as $item) {
                $product = $products->get((int) $item['product_id']);
                $qty = (int) $item['quantity'];
                $sellPrice = Money::maxZero($item['selling_price'] ?? $product->sell_price ?? 0);
                $costPrice = Money::round($product->cost_price ?? 0);
                $subTotal = Money::add($subTotal, Money::mul($sellPrice, $qty));
                $normalizedItems[] = [
                    'product' => $product,
                    'quantity' => $qty,
                    'price' => $costPrice,
                    'selling_price' => $sellPrice,
                ];
            }

            $deliveryCharge = Money::maxZero($data['delivery_charge'] ?? 0);
            $discountType = ($data['discount_type'] ?? 'fixed') === 'percent' ? 'percent' : 'fixed';
            $discountRate = $discountType === 'percent' ? Money::maxZero($data['discount_rate'] ?? 0) : null;
            $discountAmount = $discountType === 'percent'
                ? Money::min(Money::percent($subTotal, $discountRate), $subTotal)
                : Money::min(Money::maxZero($data['discount_amount'] ?? 0), $subTotal);

            $netPayable = Money::maxZero(Money::add(Money::sub($subTotal, $discountAmount), $deliveryCharge));
            $paidAmount = Money::min(Money::maxZero($data['paid_amount'] ?? 0), $netPayable);
            $dueAmount = Money::maxZero(Money::sub($netPayable, $paidAmount));

            $prevOrdersDue = Money::sum(BatteryOrder::where('customer_id', $customer->id)->pluck('due_amount'));
            $previousDueSnapshot = Money::add($customer->previous_due_amount ?? 0, $prevOrdersDue);

            $invoiceDate = Carbon::parse($data['invoice_date'] ?? now())->toDateString();
            $orderNo = $this->generateOrderNo();

            $order = BatteryOrder::create([
                'order_no' => $orderNo,
                'customer_id' => $customer->id,
                'sub_total' => $subTotal,
                'delivery_charge' => $deliveryCharge,
                'discount_amount' => $discountAmount,
                'discount_type' => $discountType,
                'discount_rate' => $discountRate,
                'return_adjustment_amount' => '0.00',
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'previous_due_amount' => $previousDueSnapshot,
                'order_note' => $data['order_note'] ?? null,
                'invoice_date' => $invoiceDate,
                'user_id' => $userId,
            ]);

            foreach ($normalizedItems as $line) {
                BatteryOrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $line['product']->id,
                    'quantity' => (string) $line['quantity'],
                    'return_quantity' => 0,
                    'price' => $line['price'],
                    'selling_price' => $line['selling_price'],
                    'user_id' => $userId,
                ]);

                $this->stockService->deduct($line['product'], $line['quantity'], 'Battery sale');
            }

            $paymentStatus = Money::cmp($dueAmount, 0) <= 0
                ? 'Paid'
                : (Money::cmp($paidAmount, 0) > 0 ? 'Partial Paid' : 'Due');

            BatteryOrderPaymentDetail::create([
                'order_id' => $order->id,
                'paid_amount' => $paidAmount,
                'discount_amount' => '0.00',
                'payment_method' => $data['payment_method'] ?? 'Cash',
                'transaction_id' => $data['transaction_id'] ?? null,
                'payment_status' => $paymentStatus,
                'due_collection_date' => $invoiceDate,
                'user_id' => $userId,
            ]);

            ActivityLogger::log(
                'Sale',
                'Battery / POS',
                "Created Battery invoice {$order->order_no} for '{$customer->customer_name}' (Net: ৳{$netPayable}, Paid: ৳{$paidAmount}, Due: ৳{$dueAmount})",
                ['order_id' => $order->id, 'order_no' => $order->order_no],
            );

            return $order->fresh(['customer', 'details.product', 'payments']);
        });
    }

    public function recordDirectPayment(int $orderId, array $data, int $userId): BatteryOrderPaymentDetail
    {
        $paidAmount = Money::maxZero($data['paid_amount'] ?? 0);
        $discountAmount = Money::maxZero($data['discount_amount'] ?? 0);

        if (Money::cmp(Money::add($paidAmount, $discountAmount), 0) <= 0) {
            throw new InvalidArgumentException('Payment or discount amount must be greater than zero.');
        }

        return DB::transaction(function () use ($orderId, $paidAmount, $discountAmount, $data, $userId): BatteryOrderPaymentDetail {
            $order = BatteryOrder::lockForUpdate()->findOrFail($orderId);
            $currentDue = Money::maxZero($order->due_amount ?? 0);

            if (Money::cmp($currentDue, 0) <= 0) {
                throw new InvalidArgumentException("Battery invoice {$order->order_no} has no remaining due.");
            }

            $appliedDiscount = Money::min($discountAmount, $currentDue);
            $afterDisc = Money::sub($currentDue, $appliedDiscount);
            $appliedPaid = Money::min($paidAmount, $afterDisc);
            $newDue = Money::maxZero(Money::sub($afterDisc, $appliedPaid));
            $newPaid = Money::add($order->paid_amount ?? 0, $appliedPaid);
            $status = Money::cmp($newDue, 0) <= 0 ? 'Paid' : 'Partial Paid';

            $payment = BatteryOrderPaymentDetail::create([
                'order_id' => $order->id,
                'paid_amount' => $appliedPaid,
                'discount_amount' => $appliedDiscount,
                'payment_method' => $data['payment_method'] ?? 'Cash',
                'transaction_id' => $data['transaction_id'] ?? null,
                'payment_status' => $status,
                'due_collection_date' => Carbon::parse($data['date'] ?? now())->toDateString(),
                'user_id' => $userId,
            ]);

            $order->paid_amount = $newPaid;
            $order->due_amount = $newDue;
            $order->save();

            ActivityLogger::log(
                'Payment',
                'Battery / Invoice',
                "Collected ৳{$appliedPaid} (discount ৳{$appliedDiscount}) on Battery invoice {$order->order_no}",
                ['order_id' => $order->id, 'payment_id' => $payment->id],
            );

            return $payment;
        });
    }

    public function updateFullInvoice(int $orderId, array $data, int $userId): BatteryOrder
    {
        $items = array_values($data['items'] ?? []);
        if (count($items) === 0) {
            throw new InvalidArgumentException('Battery invoice must contain at least one product.');
        }

        return DB::transaction(function () use ($orderId, $data, $items, $userId): BatteryOrder {
            $order = BatteryOrder::lockForUpdate()->findOrFail($orderId);
            $oldDetails = BatteryOrderDetail::where('order_id', $order->id)->orderBy('id')->lockForUpdate()->get();
            $returns = BatteryProductReturn::where('order_id', $order->id)->lockForUpdate()->get();
            $payments = BatteryOrderPaymentDetail::where('order_id', $order->id)->orderBy('id')->lockForUpdate()->get();

            $allProductIds = $oldDetails->pluck('product_id')
                ->merge(collect($items)->pluck('product_id'))
                ->map(fn ($id) => (int) $id)->unique()->sort()->values()->all();
            $products = BatteryProduct::whereIn('id', $allProductIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            $oldQtyByProduct = [];
            $returnedByProduct = [];
            foreach ($oldDetails as $od) {
                $pid = (int) $od->product_id;
                $oldQtyByProduct[$pid] = ($oldQtyByProduct[$pid] ?? 0) + (int) round((float) $od->quantity);
                $returnedByProduct[$pid] = ($returnedByProduct[$pid] ?? 0) + (int) ($od->return_quantity ?? 0);
            }
            foreach ($returns->groupBy('product_id') as $pid => $group) {
                $returnedByProduct[(int) $pid] = max($returnedByProduct[(int) $pid] ?? 0, (int) $group->sum('quantity'));
            }

            $newQtyByProduct = [];
            $subTotal = '0.00';
            $normalizedItems = [];
            foreach ($items as $item) {
                $pid = (int) $item['product_id'];
                $qty = (int) $item['quantity'];
                $sellPrice = Money::maxZero($item['selling_price']);
                if (! $products->has($pid) || $qty <= 0) {
                    throw new InvalidArgumentException('Invalid battery invoice item.');
                }
                $product = $products->get($pid);
                $costPrice = isset($item['price']) ? Money::round($item['price']) : Money::round($product->cost_price ?? 0);
                $newQtyByProduct[$pid] = ($newQtyByProduct[$pid] ?? 0) + $qty;
                $subTotal = Money::add($subTotal, Money::mul($sellPrice, $qty));
                $normalizedItems[] = [
                    'detail_id' => isset($item['id']) ? (int) $item['id'] : null,
                    'product_id' => $pid,
                    'quantity' => $qty,
                    'price' => $costPrice,
                    'selling_price' => $sellPrice,
                ];
            }

            foreach ($allProductIds as $pid) {
                $minRequired = $returnedByProduct[$pid] ?? 0;
                $proposed = $newQtyByProduct[$pid] ?? 0;
                if ($proposed < $minRequired) {
                    $pName = $products->get($pid)?->product_name ?? "#{$pid}";
                    throw new InvalidArgumentException("Cannot reduce '{$pName}' below already returned quantity ({$minRequired}).");
                }
                $oldQty = $oldQtyByProduct[$pid] ?? 0;
                $stockDelta = $oldQty - $proposed;
                if ($stockDelta < 0) {
                    $this->stockService->deduct($products->get($pid), abs($stockDelta), 'Battery invoice edit');
                } elseif ($stockDelta > 0) {
                    $this->stockService->restore($products->get($pid), $stockDelta);
                }
            }

            $existingById = $oldDetails->keyBy('id');
            $keptIds = [];
            $remainingReturnedByProduct = $returnedByProduct;
            foreach ($normalizedItems as $line) {
                $matchedDetail = null;
                if ($line['detail_id'] && $existingById->has($line['detail_id'])) {
                    $matchedDetail = $existingById->get($line['detail_id']);
                } else {
                    $matchedDetail = $oldDetails
                        ->where('product_id', $line['product_id'])
                        ->whereNotIn('id', $keptIds)
                        ->first();
                }

                $carryReturnQty = min($line['quantity'], (int) ($remainingReturnedByProduct[$line['product_id']] ?? 0));
                $remainingReturnedByProduct[$line['product_id']] = max(0, ($remainingReturnedByProduct[$line['product_id']] ?? 0) - $carryReturnQty);

                if ($matchedDetail) {
                    $matchedDetail->update([
                        'product_id' => $line['product_id'],
                        'quantity' => (string) $line['quantity'],
                        'return_quantity' => max((int) ($matchedDetail->return_quantity ?? 0), $carryReturnQty),
                        'price' => $line['price'],
                        'selling_price' => $line['selling_price'],
                    ]);
                    $keptIds[] = $matchedDetail->id;
                } else {
                    $created = BatteryOrderDetail::create([
                        'order_id' => $order->id,
                        'product_id' => $line['product_id'],
                        'quantity' => (string) $line['quantity'],
                        'return_quantity' => $carryReturnQty,
                        'price' => $line['price'],
                        'selling_price' => $line['selling_price'],
                        'user_id' => $userId,
                    ]);
                    $keptIds[] = $created->id;
                }
            }
            $oldDetails->whereNotIn('id', $keptIds)->each->delete();

            $deliveryCharge = Money::maxZero($data['delivery_charge'] ?? $order->delivery_charge ?? 0);
            $discountType = ($data['discount_type'] ?? $order->discount_type ?? 'fixed') === 'percent' ? 'percent' : 'fixed';
            $discountRate = $discountType === 'percent' ? Money::maxZero($data['discount_rate'] ?? 0) : null;
            $discountAmount = $discountType === 'percent'
                ? Money::min(Money::percent($subTotal, $discountRate), $subTotal)
                : Money::min(Money::maxZero($data['discount_amount'] ?? 0), $subTotal);
            $returnAdj = Money::maxZero($order->return_adjustment_amount ?? 0);
            $collectionDiscounts = Money::sum($payments->pluck('discount_amount'));

            $netPayable = Money::maxZero(
                Money::sub(Money::add(Money::sub($subTotal, $discountAmount), $deliveryCharge), Money::add($returnAdj, $collectionDiscounts))
            );

            $existingPaid = Money::sum($payments->pluck('paid_amount'));
            $requestedPaid = isset($data['paid_amount']) ? Money::maxZero($data['paid_amount']) : $existingPaid;
            $invoiceDate = Carbon::parse($data['invoice_date'] ?? $order->invoice_date ?? now())->toDateString();

            if (Money::cmp($requestedPaid, $existingPaid) !== 0) {
                if ($payments->count() > 1) {
                    throw new InvalidArgumentException('Cannot overwrite paid amount directly when multiple payment entries exist.');
                }
                $cappedPaid = Money::min($requestedPaid, $netPayable);
                $status = Money::cmp($cappedPaid, $netPayable) >= 0 ? 'Paid' : (Money::cmp($cappedPaid, 0) > 0 ? 'Partial Paid' : 'Due');
                if ($payments->count() === 1) {
                    $payments->first()->update([
                        'paid_amount' => $cappedPaid,
                        'payment_status' => $status,
                        'due_collection_date' => $invoiceDate,
                    ]);
                } else {
                    BatteryOrderPaymentDetail::create([
                        'order_id' => $order->id,
                        'paid_amount' => $cappedPaid,
                        'discount_amount' => '0.00',
                        'payment_method' => $data['payment_method'] ?? 'Cash',
                        'payment_status' => $status,
                        'due_collection_date' => $invoiceDate,
                        'user_id' => $userId,
                    ]);
                }
            }

            $finalPaid = Money::sum(BatteryOrderPaymentDetail::where('order_id', $order->id)->pluck('paid_amount'));
            $finalDue = Money::maxZero(Money::sub($netPayable, $finalPaid));

            $order->update([
                'customer_id' => (int) ($data['customer_id'] ?? $order->customer_id),
                'invoice_date' => $invoiceDate,
                'sub_total' => $subTotal,
                'delivery_charge' => $deliveryCharge,
                'discount_amount' => $discountAmount,
                'discount_type' => $discountType,
                'discount_rate' => $discountRate,
                'paid_amount' => $finalPaid,
                'due_amount' => $finalDue,
                'order_note' => $data['order_note'] ?? $order->order_note,
            ]);

            ActivityLogger::log('Update', 'Battery / Invoice', "Edited Battery invoice {$order->order_no}", ['order_id' => $order->id]);

            return $order->fresh(['customer', 'details.product', 'payments']);
        });
    }

    private function generateOrderNo(): string
    {
        $last = BatteryOrder::lockForUpdate()->orderByDesc('id')->first();
        $next = 1;
        if ($last) {
            if (preg_match('/(\d+)$/', (string) $last->order_no, $m)) {
                $next = max((int) $m[1], (int) $last->id) + 1;
            } else {
                $next = (int) $last->id + 1;
            }
        }

        return '#BAT-INV'.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
