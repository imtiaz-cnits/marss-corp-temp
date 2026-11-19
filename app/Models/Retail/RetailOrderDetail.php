<?php

namespace App\Models\Retail;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RetailOrderDetail extends Model
{
    protected $table = 'order_details';

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'return_quantity',
        'price',
        'selling_price',
        'user_id',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(RetailOrder::class, 'order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(RetailProduct::class, 'product_id');
    }

    public function returns(): HasMany
    {
        return $this->hasMany(RetailProductReturn::class, 'order_detail_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
