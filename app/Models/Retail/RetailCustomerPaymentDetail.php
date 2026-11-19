<?php

namespace App\Models\Retail;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetailCustomerPaymentDetail extends Model
{
    protected $table = 'customer_payment_details';

    protected $fillable = [
        'paid_amount',
        'discount_amount',
        'due_amount',
        'previous_due_amount',
        'due_collection_date',
        'payment_method',
        'transaction_id',
        'payment_status',
        'customer_id',
        'user_id',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(RetailCustomer::class, 'customer_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
