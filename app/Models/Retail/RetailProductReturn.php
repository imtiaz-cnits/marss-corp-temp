<?php

namespace App\Models\Retail;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetailProductReturn extends Model
{
    protected $table = 'product_returns';

    protected $fillable = [
        'return_number',
        'amount',
        'discount_amount',
        'due_amount',
        'quantity',
        'unit_price',
        'date',
        'order_id',
        'order_detail_id',
        'product_id',
        'customer_id',
        'user_id',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(RetailOrder::class, 'order_id');
    }

    public function orderDetail(): BelongsTo
    {
        return $this->belongsTo(RetailOrderDetail::class, 'order_detail_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(RetailCustomer::class, 'customer_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(RetailProduct::class, 'product_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
