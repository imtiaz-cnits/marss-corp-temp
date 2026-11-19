<?php

namespace App\Models\Retail;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RetailSubCategory extends Model
{
    protected $table = 'sub_categories';

    protected $fillable = [
        'sub_category_name',
        'status',
        'category_id',
        'user_id',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(RetailCategory::class, 'category_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(RetailProduct::class, 'sub_category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
