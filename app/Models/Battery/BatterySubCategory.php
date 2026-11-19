<?php

namespace App\Models\Battery;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BatterySubCategory extends Model
{
    protected $table = 'battery_sub_categories';

    protected $fillable = [
        'name',
        'category_id',
        'user_id',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BatteryCategory::class, 'category_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(BatteryProduct::class, 'sub_category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
