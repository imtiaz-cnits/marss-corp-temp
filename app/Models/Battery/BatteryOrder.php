<?php

namespace App\Models\Battery;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BatteryOrder extends Model
{
    protected $table = 'battery_orders';

    protected $fillable = [
        'order_no',
        'sub_total',
        'delivery_charge',
        'paid_amount',
        'discount_amount',
        'discount_type',
        'discount_rate',
        'due_amount',
        'previous_due_amount',
        'return_adjustment_amount',
        'order_note',
        'customer_id',
        'invoice_date',
        'user_id',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(BatteryCustomer::class, 'customer_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(BatteryOrderDetail::class, 'order_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(BatteryOrderPaymentDetail::class, 'order_id');
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(BatteryOrderPaymentDetail::class, 'order_id')->latestOfMany();
    }

    public function productReturns(): HasMany
    {
        return $this->hasMany(BatteryProductReturn::class, 'order_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
