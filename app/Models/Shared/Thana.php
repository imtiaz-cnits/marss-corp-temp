<?php

namespace App\Models\Shared;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Thana extends Model
{
    protected $table = 'thanas';

    protected $fillable = [
        'Thana_name',
        'status',
        'district_id',
        'upazila_id',
        'user_id',
    ];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function upazila(): BelongsTo
    {
        return $this->belongsTo(Upazila::class, 'upazila_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
