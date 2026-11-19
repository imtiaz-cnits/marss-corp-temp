<?php

namespace App\Models\Retail;

use App\Models\Shared\District;
use App\Models\Shared\Location;
use App\Models\Shared\Thana;
use App\Models\Shared\Upazila;
use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RetailCustomer extends Model
{
    protected $table = 'customers';

    protected $fillable = [
        'customer_id',
        'customer_name',
        'address_details',
        'date',
        'mobile',
        'email',
        'img_url',
        'nid',
        'previous_due_amount',
        'district_id',
        'upazila_id',
        'thana_id',
        'location_id',
        'user_id',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(RetailOrder::class, 'customer_id');
    }

    public function paymentDetails(): HasMany
    {
        return $this->hasMany(RetailCustomerPaymentDetail::class, 'customer_id');
    }

    public function productReturns(): HasMany
    {
        return $this->hasMany(RetailProductReturn::class, 'customer_id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function upazila(): BelongsTo
    {
        return $this->belongsTo(Upazila::class, 'upazila_id');
    }

    public function thana(): BelongsTo
    {
        return $this->belongsTo(Thana::class, 'thana_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
