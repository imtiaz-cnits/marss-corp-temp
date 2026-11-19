<?php

namespace App\Services\Battery;

use App\Models\Battery\BatteryOrder;
use App\Models\Battery\BatteryOrderDetail;
use App\Models\Battery\BatteryProduct;
use App\Models\Battery\BatteryProductReturn;
use App\Models\Battery\BatteryPurchase;
use App\Models\Battery\BatteryPurchaseOrderDetail;
use App\Models\Battery\BatteryPurchaseReturn;
use App\Models\Battery\BatterySupplier;
use App\Services\Shared\ActivityLogger;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * 100% Isolated Battery Sales & Purchase Return Service.
 */
class BatteryReturnService
{
    public function __construct(
        private readonly BatteryStockService $stockService,
    ) {}

    /**
     * @param  array<int, array{order_detail_id: int, quantity: int}>  $items
     * @return Collection<int, BatteryProductReturn>
     */
    public function createSalesReturn(int $orderId, array $items, string $date, int $userId): Collection
    {
        $items = array_values(array_filter($items, fn ($i) => (int) ($i['quantity'] ?? 0) > 0));
        if (count($items) === 0) {
            throw new InvalidArgumentException('Specify at least one battery product with return quantity > 0.');
        }

        return DB::transaction(function () use ($orderId, $items, $date, $userId): Collection {
            $order = BatteryOrder::lockForUpdate()->findOrFail($orderId);

            $requestedByDetail = [];
            foreach ($items as $item) {
                $did = (int) $item['order_detail_id'];
                $requestedByDetail[$did] = ($requestedByDetail[$did] ?? 0) + (int) $item['quantity'];
            }
            ksort($requestedByDetail);

            $details = BatteryOrderDetail::where('order_id', $order->id)
                ->whereIn('id', array_keys($requestedByDetail))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($details->count() !== count($requestedByDetail)) {
                throw new InvalidArgumentException('One or more line items do not belong to this battery invoice.');
            }

            $productIds = $details->pluck('product_id')->map(fn ($id) => (int) $id)->unique()->sort()->values()->all();
            $products = BatteryProduct::whereIn('id', $productIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            $returnNumber = $this->generateSalesReturnNumber();
            $formattedDate = Carbon::parse($date)->toDateString();

            $subTotal = Money::round($order->sub_total ?? 0);
            $orderDiscount = Money::maxZero($order->discount_amount ?? 0);
            $discountRatio = Money::cmp($subTotal, 0) > 0 && Money::cmp($orderDiscount, 0) > 0
                ? Money::div($orderDiscount, $subTotal, 6)
                : '0';

            $alreadyDiscReturned = Money::sum(BatteryProductReturn::where('order_id', $order->id)->pluck('discount_amount'));
            $remainingDiscount = Money::maxZero(Money::sub($orderDiscount, $alreadyDiscReturned));
            $remainingDue = Money::maxZero($order->due_amount ?? 0);

            $created = collect();
            $totalDueReduced = '0.00';

            foreach ($requestedByDetail as $detailId => $returnQty) {
                $detail = $details->get($detailId);
                $priorTableReturned = (int) BatteryProductReturn::where('order_id', $order->id)
                    ->where(function ($q) use ($detail) {
                        $q->where('order_detail_id', $detail->id)
                            ->orWhere(function ($q2) use ($detail) {
                                $q2->whereNull('order_detail_id')->where('product_id', $detail->product_id);
                            });
                    })
                    ->sum('quantity');

                $priorReturned = max((int) ($detail->return_quantity ?? 0), $priorTableReturned);
                $soldQty = (int) round((float) $detail->quantity);
                $available = max(0, $soldQty - $priorReturned);

                if ($returnQty > $available) {
                    $pName = $products->get((int) $detail->product_id)?->product_name ?? "#{$detail->product_id}";
                    throw new InvalidArgumentException("Cannot return {$returnQty} unit(s) of battery '{$pName}'; only {$available} returnable.");
                }

                $unitPrice = Money::round($detail->selling_price);
                $lineAmount = Money::mul($unitPrice, $returnQty);
                $proportionalDisc = (Money::cmp($subTotal, 0) > 0 && Money::cmp($orderDiscount, 0) > 0)
                    ? Money::div(Money::mul($lineAmount, $orderDiscount, 6), $subTotal, 2)
                    : '0.00';
                $discToReduce = Money::min($proportionalDisc, $remainingDiscount);
                $remainingDiscount = Money::maxZero(Money::sub($remainingDiscount, $discToReduce));

                $netLineCredit = Money::maxZero(Money::sub($lineAmount, $discToReduce));
                $dueToReduce = Money::min($netLineCredit, $remainingDue);
                $remainingDue = Money::maxZero(Money::sub($remainingDue, $dueToReduce));
                $totalDueReduced = Money::add($totalDueReduced, $dueToReduce);

                $ret = BatteryProductReturn::create([
                    'return_number' => $returnNumber,
                    'order_id' => $order->id,
                    'order_detail_id' => $detail->id,
                    'product_id' => $detail->product_id,
                    'customer_id' => $order->customer_id,
                    'quantity' => $returnQty,
                    'unit_price' => $unitPrice,
                    'amount' => $lineAmount,
                    'discount_amount' => $discToReduce,
                    'due_amount' => $dueToReduce,
                    'date' => $formattedDate,
                    'user_id' => $userId,
                ]);

                $detail->return_quantity = $priorReturned + $returnQty;
                $detail->save();

                $this->stockService->restore($products->get((int) $detail->product_id), $returnQty);
                $created->push($ret);
            }

            $order->return_adjustment_amount = Money::add($order->return_adjustment_amount ?? 0, $totalDueReduced);
            $order->due_amount = Money::maxZero(Money::sub($order->due_amount ?? 0, $totalDueReduced));
            $order->save();

            ActivityLogger::log(
                'Return',
                'Battery / Sales Return',
                "Processed Battery sales return {$returnNumber} for invoice {$order->order_no} (Due adjusted: ৳{$totalDueReduced})",
                ['order_id' => $order->id, 'return_number' => $returnNumber],
            );

            return $created;
        });
    }

    /**
     * @param  array<int, array{purchase_order_detail_id: int, quantity: int}>  $items
     * @return Collection<int, BatteryPurchaseReturn>
     */
    public function createPurchaseReturn(int $purchaseId, array $items, string $date, int $userId): Collection
    {
        $items = array_values(array_filter($items, fn ($i) => (int) ($i['quantity'] ?? 0) > 0));
        if (count($items) === 0) {
            throw new InvalidArgumentException('Specify at least one battery product with return quantity > 0.');
        }

        return DB::transaction(function () use ($purchaseId, $items, $date, $userId): Collection {
            $purchase = BatteryPurchase::lockForUpdate()->findOrFail($purchaseId);
            BatterySupplier::lockForUpdate()->findOrFail($purchase->supplier_id);

            $requestedByDetail = [];
            foreach ($items as $item) {
                $did = (int) $item['purchase_order_detail_id'];
                $requestedByDetail[$did] = ($requestedByDetail[$did] ?? 0) + (int) $item['quantity'];
            }
            ksort($requestedByDetail);

            $details = BatteryPurchaseOrderDetail::where('purchase_id', $purchase->id)
                ->whereIn('id', array_keys($requestedByDetail))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($details->count() !== count($requestedByDetail)) {
                throw new InvalidArgumentException('One or more line items do not belong to this battery purchase.');
            }

            $productIds = $details->pluck('product_id')->map(fn ($id) => (int) $id)->unique()->sort()->values()->all();
            $products = BatteryProduct::whereIn('id', $productIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            $returnNumber = $this->generatePurchaseReturnNumber();
            $formattedDate = Carbon::parse($date)->toDateString();

            $remainingDue = Money::maxZero($purchase->due_amount ?? 0);
            $totalDueReduced = '0.00';
            $created = collect();

            foreach ($requestedByDetail as $detailId => $returnQty) {
                $detail = $details->get($detailId);
                $boughtQty = (int) round((float) $detail->quantity);
                $priorReturned = (int) ($detail->return_quantity ?? 0);
                $available = max(0, $boughtQty - $priorReturned);

                if ($returnQty > $available) {
                    $pName = $products->get((int) $detail->product_id)?->product_name ?? "#{$detail->product_id}";
                    throw new InvalidArgumentException("Cannot return {$returnQty} unit(s) of battery '{$pName}'; only {$available} returnable.");
                }

                $product = $products->get((int) $detail->product_id);
                $this->stockService->deduct($product, $returnQty, 'Battery purchase return');

                $unitCost = Money::round($detail->cost_price);
                $lineAmount = Money::mul($unitCost, $returnQty);
                $dueReduced = Money::min($lineAmount, $remainingDue);
                $remainingDue = Money::maxZero(Money::sub($remainingDue, $dueReduced));
                $totalDueReduced = Money::add($totalDueReduced, $dueReduced);

                $ret = BatteryPurchaseReturn::create([
                    'return_number' => $returnNumber,
                    'purchase_id' => $purchase->id,
                    'purchase_order_detail_id' => $detail->id,
                    'supplier_id' => $purchase->supplier_id,
                    'product_id' => $detail->product_id,
                    'quantity' => $returnQty,
                    'unit_price' => $unitCost,
                    'amount' => $lineAmount,
                    'discount_amount' => '0.00',
                    'due_amount' => $dueReduced,
                    'date' => $formattedDate,
                    'user_id' => $userId,
                ]);

                $detail->return_quantity = $priorReturned + $returnQty;
                $detail->save();
                $created->push($ret);
            }

            $purchase->return_adjustment_amount = Money::add($purchase->return_adjustment_amount ?? 0, $totalDueReduced);
            $purchase->due_amount = Money::maxZero(Money::sub($purchase->due_amount ?? 0, $totalDueReduced));
            $purchase->purchase_payable_amount = $purchase->due_amount;
            $purchase->save();

            ActivityLogger::log(
                'Return',
                'Battery / Purchase Return',
                "Processed Battery purchase return {$returnNumber} for {$purchase->purchase_id} (Due adjusted: ৳{$totalDueReduced})",
                ['purchase_id' => $purchase->id, 'return_number' => $returnNumber],
            );

            return $created;
        });
    }

    private function generateSalesReturnNumber(): string
    {
        $max = (int) (BatteryProductReturn::lockForUpdate()->max('id') ?? 0);

        return '#BRET'.str_pad((string) ($max + 1), 5, '0', STR_PAD_LEFT);
    }

    private function generatePurchaseReturnNumber(): string
    {
        $max = (int) (BatteryPurchaseReturn::lockForUpdate()->max('id') ?? 0);

        return '#BPRET'.str_pad((string) ($max + 1), 5, '0', STR_PAD_LEFT);
    }
}
