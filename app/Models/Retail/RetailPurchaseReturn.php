<?php

namespace App\Models\Retail;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetailPurchaseReturn extends Model
{
    protected $table = 'purchase_returns';

    protected $fillable = [
        'return_number',
        'purchase_id',
        'purchase_order_detail_id',
        'supplier_id',
        'product_id',
        'user_id',
        'amount',
        'discount_amount',
        'due_amount',
        'quantity',
        'unit_price',
        'date',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(RetailPurchase::class, 'purchase_id');
    }

    public function purchaseOrderDetail(): BelongsTo
    {
        return $this->belongsTo(RetailPurchaseOrderDetail::class, 'purchase_order_detail_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(RetailSupplier::class, 'supplier_id');
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
