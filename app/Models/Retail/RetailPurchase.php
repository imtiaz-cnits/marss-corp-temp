<?php

namespace App\Models\Retail;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RetailPurchase extends Model
{
    protected $table = 'purchases';

    protected $fillable = [
        'purchase_id',
        'referance_no',
        'paid_amount',
        'due_amount',
        'purchase_payable_amount',
        'date',
        'grand_subtotal',
        'delivery_charge',
        'discount_amount',
        'discount_type',
        'discount_rate',
        'return_adjustment_amount',
        'attach_document',
        'supplier_id',
        'user_id',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(RetailSupplier::class, 'supplier_id');
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(RetailPurchaseOrderDetail::class, 'purchase_id');
    }

    public function paymentDetails(): HasMany
    {
        return $this->hasMany(RetailPurchasePaymentDetail::class, 'purchases_id');
    }

    public function purchaseReturns(): HasMany
    {
        return $this->hasMany(RetailPurchaseReturn::class, 'purchase_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
