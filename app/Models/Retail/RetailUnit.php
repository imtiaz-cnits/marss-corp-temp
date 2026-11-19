<?php

namespace App\Models\Retail;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RetailUnit extends Model
{
    protected $table = 'units';

    protected $fillable = [
        'unit_name',
        'status',
        'user_id',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(RetailProduct::class, 'unit_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
