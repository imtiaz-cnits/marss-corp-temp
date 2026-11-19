<?php

namespace App\Models\Battery;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BatteryUnit extends Model
{
    protected $table = 'battery_units';

    protected $fillable = [
        'unit_name',
        'user_id',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(BatteryProduct::class, 'unit_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
