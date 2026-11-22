<?php

namespace App\Models\Battery;

use App\Models\Shared\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BatteryProduct extends Model
{
    protected $table = 'battery_products';

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
        'sub_category_id',
        'unit_id',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'product_code' => 'array',
        ];
    }

    /**
     * Safely decode product_code regardless of legacy double-encoding.
     *
     * @return list<string>
     */
    public function decodedCodes(): array
    {
        $raw = $this->product_code;
        $list = [];

        if (is_array($raw)) {
            $list = $raw;
        } elseif (is_string($raw)) {
            $trimmed = trim($raw);
            if ($trimmed !== '') {
                $decoded = json_decode($trimmed, true);
                if (is_string($decoded)) {
                    $decoded = json_decode($decoded, true);
                }
                if (is_array($decoded)) {
                    $list = $decoded;
                } else {
                    $list = [$trimmed];
                }
            }
        }

        $clean = [];
        foreach ($list as $item) {
            $s = trim((string) $item);
            $s = trim($s, " \t\n\r\0\x0B\"'[]");
            if ($s === '' || in_array(strtolower($s), ['null', 'undefined', '[]', '{}'], true)) {
                continue;
            }
            $clean[] = $s;
        }

        return array_values(array_unique($clean));
    }

    public function primaryCode(): string
    {
        return $this->decodedCodes()[0] ?? '';
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(BatteryBrand::class, 'brand_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BatteryCategory::class, 'category_id');
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(BatterySubCategory::class, 'sub_category_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(BatteryUnit::class, 'unit_id');
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(BatteryOrderDetail::class, 'product_id');
    }

    public function purchaseOrderDetails(): HasMany
    {
        return $this->hasMany(BatteryPurchaseOrderDetail::class, 'product_id');
    }

    public function productReturns(): HasMany
    {
        return $this->hasMany(BatteryProductReturn::class, 'product_id');
    }

    public function purchaseReturns(): HasMany
    {
        return $this->hasMany(BatteryPurchaseReturn::class, 'product_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
