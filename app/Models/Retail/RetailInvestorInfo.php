<?php

namespace App\Models\Retail;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RetailInvestorInfo extends Model
{
    protected $table = 'investor_infos';

    protected $fillable = [
        'investor_id',
        'name',
        'mobile',
        'address',
        'email',
        'status',
        'user_id',
    ];

    public function invests(): HasMany
    {
        return $this->hasMany(RetailInvest::class, 'investor_info_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
