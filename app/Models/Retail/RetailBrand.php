<?php

namespace App\Models\Retail;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RetailBrand extends Model
{
    protected $table = 'brands';

    protected $fillable = [
        'name',
        'logo',
        'status',
        'user_id',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(RetailProduct::class, 'brand_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
