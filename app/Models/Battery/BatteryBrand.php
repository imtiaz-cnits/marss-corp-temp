<?php

namespace App\Models\Battery;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BatteryBrand extends Model
{
    protected $table = 'battery_brands';

    protected $fillable = [
        'name',
        'img_url',
        'user_id',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(BatteryProduct::class, 'brand_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
