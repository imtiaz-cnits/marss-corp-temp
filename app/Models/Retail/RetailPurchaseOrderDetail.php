<?php

namespace App\Models\Retail;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetailPurchaseOrderDetail extends Model
{
    protected $table = 'purchase_order_details';

    protected $fillable = [
        'product_id',
        'purchase_id',
        'quantity',
        'return_quantity',
        'cost_price',
        'subtotal',
        'user_id',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(RetailPurchase::class, 'purchase_id');
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
