<?php

namespace App\Models\Retail;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetailInvest extends Model
{
    protected $table = 'invests';

    protected $fillable = [
        'invest_amount',
        'invest_details',
        'date',
        'investor_info_id',
        'user_id',
    ];

    public function investor(): BelongsTo
    {
        return $this->belongsTo(RetailInvestorInfo::class, 'investor_info_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
