<?php

namespace App\Models\Retail;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetailSupplierDueCollection extends Model
{
    protected $table = 'supplier_due_collections';

    protected $fillable = [
        'paid_amount',
        'due_amount',
        'discount_amount',
        'purchase_payable_amount',
        'payment_method',
        'transaction_id',
        'payment_status',
        'due_collection_date',
        'supplier_id',
        'user_id',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(RetailSupplier::class, 'supplier_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
