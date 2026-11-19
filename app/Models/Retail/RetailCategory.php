<?php

namespace App\Models\Retail;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RetailCategory extends Model
{
    protected $table = 'categories';

    protected $fillable = [
        'category_name',
        'img_url',
        'status',
        'user_id',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(RetailProduct::class, 'category_id');
    }

    public function subCategories(): HasMany
    {
        return $this->hasMany(RetailSubCategory::class, 'category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
