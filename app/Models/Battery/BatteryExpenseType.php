<?php

namespace App\Models\Battery;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BatteryExpenseType extends Model
{
    protected $table = 'battery_expense_types';

    protected $fillable = [
        'type_name',
        'user_id',
    ];

    public function expenses(): HasMany
    {
        return $this->hasMany(BatteryExpense::class, 'expense_type_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
