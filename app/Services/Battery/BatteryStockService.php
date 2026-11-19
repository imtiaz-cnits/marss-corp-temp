<?php

namespace App\Services\Battery;

use App\Models\Battery\BatteryProduct;
use App\Models\Battery\BatteryPurchaseOrderDetail;
use App\Services\Shared\ActivityLogger;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * 100% Isolated Battery Stock Service — queries ONLY battery_* tables.
 */
class BatteryStockService
{
    public function increaseForPurchase(BatteryProduct $product, int|float|string $qty, int|float|string $unitCost): void
    {
        $existingQty = (int) round((float) ($product->quantity ?? 0));
        $addQty = (int) round((float) $qty);
        $existingCost = Money::normalize($product->cost_price);
        $purchaseCost = Money::normalize($unitCost);

        if ($addQty <= 0) {
            throw new InvalidArgumentException("Purchase quantity must be greater than zero for battery product #{$product->id}.");
        }

        $newQty = $existingQty + $addQty;

        if ($existingQty > 0 && $newQty > 0) {
            $oldTotal = Money::mul($existingCost, $existingQty, 6);
            $newTotal = Money::mul($purchaseCost, $addQty, 6);
            $newCost = Money::div(Money::add($oldTotal, $newTotal, 6), $newQty, 2);
        } else {
            $newCost = Money::round($purchaseCost, 2);
        }

        $product->quantity = (string) $newQty;
        $product->cost_price = $newCost;
        $product->status = $newQty > 0 ? 'Available' : 'Out of Stock';
        $product->save();
    }

    public function applyPurchaseDelta(
        BatteryProduct $product,
        int $oldQty,
        string $oldCost,
        int $newQty,
        string $newCost,
    ): void {
        $currentStock = (int) round((float) ($product->quantity ?? 0));
        $currentCost = Money::normalize($product->cost_price);
        $deltaQty = $newQty - $oldQty;
        $finalStock = $currentStock + $deltaQty;

        if ($deltaQty < 0 && $finalStock < 0) {
            throw new InvalidArgumentException("Insufficient battery stock for '{$product->product_name}'. Current: {$currentStock}, reduction: ".abs($deltaQty));
        }

        $currentValue = Money::mul($currentCost, max(0, $currentStock), 6);
        $deltaValue = Money::sub(Money::mul($newCost, $newQty, 6), Money::mul($oldCost, $oldQty, 6), 6);
        $finalValue = Money::add($currentValue, $deltaValue, 6);

        if ($finalStock > 0 && Money::cmp($finalValue, 0) > 0) {
            $product->cost_price = Money::div($finalValue, $finalStock, 2);
        } elseif (Money::cmp($newCost, 0) > 0) {
            $product->cost_price = Money::round($newCost, 2);
        }

        $product->quantity = (string) $finalStock;
        $product->status = $finalStock > 0 ? 'Available' : 'Out of Stock';
        $product->save();
    }

    public function reverseForPurchaseDelete(
        BatteryProduct $product,
        int $qtyToReverse,
        string $purchaseCost,
        int $excludePurchaseId,
    ): void {
        $currentQty = (int) round((float) ($product->quantity ?? 0));
        if ($qtyToReverse <= 0) {
            return;
        }

        if ($currentQty < $qtyToReverse) {
            throw new InvalidArgumentException("Cannot reverse {$qtyToReverse} unit(s) of battery product '{$product->product_name}'; only {$currentQty} in stock.");
        }

        $newQty = $currentQty - $qtyToReverse;
        $currentCost = Money::normalize($product->cost_price);

        if ($newQty > 0) {
            $remainingValue = Money::sub(
                Money::mul($currentCost, $currentQty, 6),
                Money::mul($purchaseCost, $qtyToReverse, 6),
                6,
            );
            if (Money::cmp($remainingValue, 0) > 0) {
                $product->cost_price = Money::div($remainingValue, $newQty, 2);
            } else {
                $product->cost_price = $this->fallbackLatestPurchaseCost($product->id, $excludePurchaseId, $currentCost);
            }
        } else {
            $product->cost_price = $this->fallbackLatestPurchaseCost($product->id, $excludePurchaseId, $currentCost);
        }

        $product->quantity = (string) $newQty;
        $product->status = $newQty > 0 ? 'Available' : 'Out of Stock';
        $product->save();
    }

    public function deduct(BatteryProduct $product, int $qty, string $context = 'battery sale'): void
    {
        if ($qty <= 0) {
            throw new InvalidArgumentException("Quantity must be greater than zero for {$context}.");
        }

        $available = (int) round((float) ($product->quantity ?? 0));
        if ($available < $qty) {
            throw new InvalidArgumentException("Insufficient battery stock for '{$product->product_name}'. Available: {$available}, Requested: {$qty}.");
        }

        $newQty = $available - $qty;
        $product->quantity = (string) $newQty;
        $product->status = $newQty > 0 ? 'Available' : 'Out of Stock';
        $product->save();
    }

    public function restore(BatteryProduct $product, int $qty): void
    {
        if ($qty <= 0) {
            return;
        }

        $current = (int) round((float) ($product->quantity ?? 0));
        $newQty = $current + $qty;
        $product->quantity = (string) $newQty;
        $product->status = $newQty > 0 ? 'Available' : 'Out of Stock';
        $product->save();
    }

    public function manualAdjust(int $productId, int $newQuantity, ?string $reason = null): BatteryProduct
    {
        if ($newQuantity < 0) {
            throw new InvalidArgumentException('Battery stock quantity cannot be negative.');
        }

        return DB::transaction(function () use ($productId, $newQuantity, $reason): BatteryProduct {
            $product = BatteryProduct::lockForUpdate()->findOrFail($productId);
            $oldQuantity = (int) round((float) ($product->quantity ?? 0));
            $delta = $newQuantity - $oldQuantity;

            $product->quantity = (string) $newQuantity;
            $product->status = $newQuantity > 0 ? 'Available' : 'Out of Stock';
            $product->save();

            ActivityLogger::log(
                'Update',
                'Battery / Stock Overview',
                "Adjusted battery stock for '{$product->product_name}' from {$oldQuantity} to {$newQuantity} (delta: {$delta})",
                ['product_id' => $product->id, 'old_qty' => $oldQuantity, 'new_qty' => $newQuantity, 'delta' => $delta, 'reason' => $reason],
            );

            return $product;
        });
    }

    /**
     * @return array{total_products: int, total_units: int, cost_value: string, sell_value: string, potential_margin: string, markup_percent: float, out_of_stock: int, low_stock: int, available: int}
     */
    public function summaryMetrics(): array
    {
        $row = DB::table('battery_products')->selectRaw('
            COUNT(*) as total_products,
            COALESCE(SUM(CAST(COALESCE(quantity, 0) AS SIGNED)), 0) as total_units,
            COALESCE(SUM(CASE WHEN CAST(COALESCE(quantity, 0) AS SIGNED) > 0 THEN CAST(COALESCE(cost_price, 0) AS DECIMAL(15,2)) * CAST(COALESCE(quantity, 0) AS DECIMAL(15,2)) ELSE 0 END), 0) as cost_value,
            COALESCE(SUM(CASE WHEN CAST(COALESCE(quantity, 0) AS SIGNED) > 0 THEN CAST(COALESCE(sell_price, 0) AS DECIMAL(15,2)) * CAST(COALESCE(quantity, 0) AS DECIMAL(15,2)) ELSE 0 END), 0) as sell_value,
            SUM(CASE WHEN CAST(COALESCE(quantity, 0) AS SIGNED) <= 0 THEN 1 ELSE 0 END) as out_of_stock,
            SUM(CASE WHEN CAST(COALESCE(quantity, 0) AS SIGNED) > 0 AND CAST(COALESCE(quantity, 0) AS SIGNED) <= 5 THEN 1 ELSE 0 END) as low_stock,
            SUM(CASE WHEN CAST(COALESCE(quantity, 0) AS SIGNED) > 5 THEN 1 ELSE 0 END) as available
        ')->first();

        $costVal = Money::round($row->cost_value ?? 0);
        $sellVal = Money::round($row->sell_value ?? 0);
        $margin = Money::sub($sellVal, $costVal);
        $markup = Money::cmp($costVal, 0) > 0 ? (float) Money::div(Money::mul($margin, 100, 4), $costVal, 1) : 0.0;

        return [
            'total_products' => (int) ($row->total_products ?? 0),
            'total_units' => (int) ($row->total_units ?? 0),
            'cost_value' => $costVal,
            'sell_value' => $sellVal,
            'potential_margin' => $margin,
            'markup_percent' => $markup,
            'out_of_stock' => (int) ($row->out_of_stock ?? 0),
            'low_stock' => (int) ($row->low_stock ?? 0),
            'available' => (int) ($row->available ?? 0),
        ];
    }

    private function fallbackLatestPurchaseCost(int $productId, int $excludePurchaseId, string $defaultCost): string
    {
        $latest = BatteryPurchaseOrderDetail::where('product_id', $productId)
            ->where('purchase_id', '!=', $excludePurchaseId)
            ->latest('id')
            ->first();

        return $latest ? Money::round($latest->cost_price, 2) : Money::round($defaultCost, 2);
    }
}
