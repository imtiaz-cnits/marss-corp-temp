<?php

namespace App\Models\Retail;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RetailProduct extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'img_url',
        'product_name',
        'quantity',
        'cost_price',
        'sell_price',
        'status',
        'product_code',
        'brand_id',
        'category_id',
        'door_side',
        'sub_category_id',
        'unit_id',
        'user_id',
    ];

    public function setProductCodeAttribute(mixed $value): void
    {
        if (is_array($value)) {
            $this->attributes['product_code'] = json_encode(array_values(array_map('strval', $value)));

            return;
        }

        $str = trim((string) $value);
        if ($str !== '' && (str_starts_with($str, '[') || str_starts_with($str, '"'))) {
            json_decode($str);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->attributes['product_code'] = $str;

                return;
            }
        }

        $this->attributes['product_code'] = json_encode($str !== '' ? [$str] : []);
    }

    /**
     * Safely decode legacy JSON / double-encoded JSON / plain string product_code to a clean array.
     *
     * @return list<string>
     */
    public function decodedCodes(): array
    {
        $raw = $this->product_code;

        if (is_array($raw)) {
            return array_values(array_filter(array_map('strval', $raw)));
        }

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (is_string($decoded)) {
                $decoded = json_decode($decoded, true);
            }
            if (is_array($decoded)) {
                return array_values(array_filter(array_map('strval', $decoded)));
            }
            if (trim($raw) !== '') {
                return [trim($raw)];
            }
        }

        return [];
    }

    public function primaryCode(): string
    {
        return $this->decodedCodes()[0] ?? '';
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(RetailBrand::class, 'brand_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(RetailCategory::class, 'category_id');
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(RetailSubCategory::class, 'sub_category_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(RetailUnit::class, 'unit_id');
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(RetailOrderDetail::class, 'product_id');
    }

    public function purchaseOrderDetails(): HasMany
    {
        return $this->hasMany(RetailPurchaseOrderDetail::class, 'product_id');
    }

    public function productReturns(): HasMany
    {
        return $this->hasMany(RetailProductReturn::class, 'product_id');
    }

    public function purchaseReturns(): HasMany
    {
        return $this->hasMany(RetailPurchaseReturn::class, 'product_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
