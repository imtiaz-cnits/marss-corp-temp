<?php

namespace App\Models\Battery;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BatteryOrderDetail extends Model
{
    protected $table = 'battery_order_details';

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'return_quantity',
        'price',
        'selling_price',
        'user_id',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(BatteryOrder::class, 'order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(BatteryProduct::class, 'product_id');
    }

    public function returns(): HasMany
    {
        return $this->hasMany(BatteryProductReturn::class, 'order_detail_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
