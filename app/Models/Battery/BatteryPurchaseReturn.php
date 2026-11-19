<?php

namespace App\Models\Battery;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatteryPurchaseReturn extends Model
{
    protected $table = 'battery_purchase_returns';

    protected $fillable = [
        'return_number',
        'purchase_id',
        'purchase_order_detail_id',
        'supplier_id',
        'product_id',
        'user_id',
        'amount',
        'discount_amount',
        'due_amount',
        'quantity',
        'unit_price',
        'date',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(BatteryPurchase::class, 'purchase_id');
    }

    public function purchaseOrderDetail(): BelongsTo
    {
        return $this->belongsTo(BatteryPurchaseOrderDetail::class, 'purchase_order_detail_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(BatterySupplier::class, 'supplier_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(BatteryProduct::class, 'product_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
