<?php

namespace App\Models\Retail;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetailOrderPaymentDetail extends Model
{
    protected $table = 'order_payment_details';

    protected $fillable = [
        'order_id',
        'paid_amount',
        'discount_amount',
        'transaction_id',
        'payment_method',
        'due_collection_date',
        'payment_status',
        'user_id',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(RetailOrder::class, 'order_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
