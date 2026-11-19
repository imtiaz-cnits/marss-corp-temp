<?php

namespace App\Models\Battery;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatteryPurchaseOrderDetail extends Model
{
    protected $table = 'battery_purchase_order_details';

    protected $fillable = [
        'purchase_id',
        'product_id',
        'quantity',
        'return_quantity',
        'cost_price',
        'subtotal',
        'user_id',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(BatteryPurchase::class, 'purchase_id');
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
