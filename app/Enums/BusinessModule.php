<?php

namespace App\Enums;

enum BusinessModule: string
{
    case RETAIL = 'retail';
    case BATTERY = 'battery';

    public function label(): string
    {
        return match ($this) {
            self::RETAIL => 'Retail Shop',
            self::BATTERY => 'Battery',
        };
    }
}
