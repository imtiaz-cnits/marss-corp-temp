<?php

namespace App\Models\Battery;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BatteryPurchase extends Model
{
    protected $table = 'battery_purchases';

    protected $fillable = [
        'purchase_id',
        'purchase_payable_amount',
        'paid_amount',
        'due_amount',
        'delivery_charge',
        'discount_amount',
        'discount_type',
        'discount_rate',
        'return_adjustment_amount',
        'referance_no',
        'date',
        'grand_subtotal',
        'attach_document',
        'supplier_id',
        'user_id',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(BatterySupplier::class, 'supplier_id');
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(BatteryPurchaseOrderDetail::class, 'purchase_id');
    }

    public function paymentDetails(): HasMany
    {
        return $this->hasMany(BatteryPurchasePaymentDetail::class, 'purchases_id');
    }

    public function purchaseReturns(): HasMany
    {
        return $this->hasMany(BatteryPurchaseReturn::class, 'purchase_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
