<?php

namespace App\Models\Battery;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatteryPurchasePaymentDetail extends Model
{
    protected $table = 'battery_purchase_payment_details';

    protected $fillable = [
        'purchases_id',
        'paid_amount',
        'discount_amount',
        'transaction_id',
        'purchase_due_collection_date',
        'payment_method',
        'payment_status',
        'user_id',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(BatteryPurchase::class, 'purchases_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
