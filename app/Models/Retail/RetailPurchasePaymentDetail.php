<?php

namespace App\Models\Retail;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetailPurchasePaymentDetail extends Model
{
    protected $table = 'purchase_payment_details';

    protected $fillable = [
        'purchases_id',
        'paid_amount',
        'discount_amount',
        'payment_method',
        'payment_status',
        'transaction_id',
        'purchase_due_collection_date',
        'purchase_order_details_id',
        'user_id',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(RetailPurchase::class, 'purchases_id');
    }

    public function orderDetail(): BelongsTo
    {
        return $this->belongsTo(RetailPurchaseOrderDetail::class, 'purchase_order_details_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
