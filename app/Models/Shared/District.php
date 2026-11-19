<?php

namespace App\Models\Shared;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    protected $table = 'districts';

    protected $fillable = [
        'district_name',
        'status',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function upazilas(): HasMany
    {
        return $this->hasMany(Upazila::class, 'district_id');
    }

    public function thanas(): HasMany
    {
        return $this->hasMany(Thana::class, 'district_id');
    }
}
