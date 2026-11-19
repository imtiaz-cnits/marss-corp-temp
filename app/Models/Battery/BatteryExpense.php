<?php

namespace App\Models\Battery;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatteryExpense extends Model
{
    protected $table = 'battery_expenses';

    protected $fillable = [
        'expense_type_id',
        'expense_amount',
        'expense_details',
        'date',
        'user_id',
        'staff_id',
    ];

    public function expenseType(): BelongsTo
    {
        return $this->belongsTo(BatteryExpenseType::class, 'expense_type_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
