<?php

namespace App\Models\Retail;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RetailExpenseType extends Model
{
    protected $table = 'expense_types';

    protected $fillable = [
        'type_name',
        'status',
        'user_id',
    ];

    public function expenses(): HasMany
    {
        return $this->hasMany(RetailExpense::class, 'expense_type_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
