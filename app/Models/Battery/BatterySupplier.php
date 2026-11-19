<?php

namespace App\Models\Battery;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class BatterySupplier extends Model
{
    protected $table = 'battery_suppliers';

    protected $fillable = [
        'supplier_id',
        'name',
        'company',
        'mobile',
        'address',
        'img_url',
        'purchase_payable_amount',
        'user_id',
    ];

    public function purchases(): HasMany
    {
        return $this->hasMany(BatteryPurchase::class, 'supplier_id');
    }

    public function dueCollections(): HasMany
    {
        return $this->hasMany(BatterySupplierDueCollection::class, 'supplier_id');
    }

    public function purchaseReturns(): HasMany
    {
        return $this->hasMany(BatteryPurchaseReturn::class, 'supplier_id');
    }

    public function purchasePaymentDetails(): HasManyThrough
    {
        return $this->hasManyThrough(
            BatteryPurchasePaymentDetail::class,
            BatteryPurchase::class,
            'supplier_id',
            'purchases_id',
            'id',
            'id',
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
