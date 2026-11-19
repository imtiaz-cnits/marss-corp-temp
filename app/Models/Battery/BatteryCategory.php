<?php

namespace App\Models\Battery;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BatteryCategory extends Model
{
    protected $table = 'battery_categories';

    protected $fillable = [
        'name',
        'img_url',
        'user_id',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(BatteryProduct::class, 'category_id');
    }

    public function subCategories(): HasMany
    {
        return $this->hasMany(BatterySubCategory::class, 'category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
