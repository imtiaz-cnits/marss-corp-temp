<?php

namespace App\Models\Battery;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatterySupplierDueCollection extends Model
{
    protected $table = 'battery_supplier_due_collections';

    protected $fillable = [
        'paid_amount',
        'discount_amount',
        'due_amount',
        'payment_date',
        'payment_method',
        'transaction_id',
        'supplier_id',
        'user_id',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(BatterySupplier::class, 'supplier_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
