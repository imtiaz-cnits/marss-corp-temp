<?php

namespace App\Models\Retail;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class RetailSupplier extends Model
{
    protected $table = 'suppliers';

    protected $fillable = [
        'supplier_id',
        'name',
        'company',
        'mobile',
        'address',
        'email',
        'img_url',
        'purchase_payable_amount',
        'status',
        'user_id',
    ];

    public function purchases(): HasMany
    {
        return $this->hasMany(RetailPurchase::class, 'supplier_id');
    }

    public function dueCollections(): HasMany
    {
        return $this->hasMany(RetailSupplierDueCollection::class, 'supplier_id');
    }

    public function purchaseReturns(): HasMany
    {
        return $this->hasMany(RetailPurchaseReturn::class, 'supplier_id');
    }

    public function purchasePaymentDetails(): HasManyThrough
    {
        return $this->hasManyThrough(
            RetailPurchasePaymentDetail::class,
            RetailPurchase::class,
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
