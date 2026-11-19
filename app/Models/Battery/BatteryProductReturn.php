<?php

namespace App\Models\Battery;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatteryProductReturn extends Model
{
    protected $table = 'battery_product_returns';

    protected $fillable = [
        'return_number',
        'amount',
        'discount_amount',
        'due_amount',
        'date',
        'quantity',
        'unit_price',
        'order_id',
        'order_detail_id',
        'customer_id',
        'product_id',
        'user_id',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(BatteryOrder::class, 'order_id');
    }

    public function orderDetail(): BelongsTo
    {
        return $this->belongsTo(BatteryOrderDetail::class, 'order_detail_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(BatteryCustomer::class, 'customer_id');
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
