<?php

namespace App\Models\Retail;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RetailOrder extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'order_no',
        'sub_total',
        'return_adjustment_amount',
        'paid_amount',
        'delivery_charge',
        'discount_amount',
        'discount_type',
        'discount_rate',
        'due_amount',
        'previous_due_amount',
        'order_note',
        'customer_id',
        'invoice_date',
        'user_id',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(RetailCustomer::class, 'customer_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(RetailOrderDetail::class, 'order_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(RetailOrderPaymentDetail::class, 'order_id');
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(RetailOrderPaymentDetail::class, 'order_id')->latestOfMany();
    }

    public function productReturns(): HasMany
    {
        return $this->hasMany(RetailProductReturn::class, 'order_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
