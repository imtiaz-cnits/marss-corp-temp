<?php

namespace App\Support;

/**
 * Arbitrary-precision decimal arithmetic helper using bcmath.
 * Guarantees zero IEEE-754 floating-point drift on financial calculations.
 */
final class Money
{
    public const SCALE = 2;

    public const CALC_SCALE = 6;

    public static function normalize(int|float|string|null $value, int $scale = self::CALC_SCALE): string
    {
        if ($value === null || $value === '') {
            return bcadd('0', '0', $scale);
        }

        $clean = is_string($value) ? str_replace([',', '৳', ' '], '', trim($value)) : (string) $value;

        if (! is_numeric($clean)) {
            return bcadd('0', '0', $scale);
        }

        // Convert scientific notation safely (e.g. 1.5E-5)
        if (stripos($clean, 'e') !== false) {
            $clean = number_format((float) $clean, $scale + 2, '.', '');
        }

        return bcadd($clean, '0', $scale);
    }

    public static function round(int|float|string|null $value, int $scale = self::SCALE): string
    {
        $norm = self::normalize($value, $scale + 4);
        $negative = str_starts_with($norm, '-');
        $abs = $negative ? substr($norm, 1) : $norm;
        $half = '0.'.str_repeat('0', $scale).'5';
        $rounded = bcadd($abs, $half, $scale);

        if ($negative && bccomp($rounded, '0', $scale) !== 0) {
            return '-'.$rounded;
        }

        return $rounded;
    }

    public static function add(int|float|string|null $a, int|float|string|null $b, int $scale = self::SCALE): string
    {
        return self::round(bcadd(self::normalize($a), self::normalize($b), self::CALC_SCALE), $scale);
    }

    public static function sub(int|float|string|null $a, int|float|string|null $b, int $scale = self::SCALE): string
    {
        return self::round(bcsub(self::normalize($a), self::normalize($b), self::CALC_SCALE), $scale);
    }

    public static function mul(int|float|string|null $a, int|float|string|null $b, int $scale = self::SCALE): string
    {
        return self::round(bcmul(self::normalize($a), self::normalize($b), self::CALC_SCALE), $scale);
    }

    public static function div(int|float|string|null $a, int|float|string|null $b, int $scale = self::SCALE): string
    {
        $divisor = self::normalize($b);
        if (bccomp($divisor, '0', self::CALC_SCALE) === 0) {
            return bcadd('0', '0', $scale);
        }

        return self::round(bcdiv(self::normalize($a), $divisor, self::CALC_SCALE + 2), $scale);
    }

    public static function percent(int|float|string|null $amount, int|float|string|null $rate, int $scale = self::SCALE): string
    {
        $product = bcmul(self::normalize($amount), self::normalize($rate), self::CALC_SCALE);

        return self::round(bcdiv($product, '100', self::CALC_SCALE + 2), $scale);
    }

    public static function maxZero(int|float|string|null $value, int $scale = self::SCALE): string
    {
        $rounded = self::round($value, $scale);

        return bccomp($rounded, '0', $scale) < 0 ? bcadd('0', '0', $scale) : $rounded;
    }

    public static function min(int|float|string|null $a, int|float|string|null $b, int $scale = self::SCALE): string
    {
        $ra = self::round($a, $scale);
        $rb = self::round($b, $scale);

        return bccomp($ra, $rb, $scale) <= 0 ? $ra : $rb;
    }

    public static function max(int|float|string|null $a, int|float|string|null $b, int $scale = self::SCALE): string
    {
        $ra = self::round($a, $scale);
        $rb = self::round($b, $scale);

        return bccomp($ra, $rb, $scale) >= 0 ? $ra : $rb;
    }

    public static function cmp(int|float|string|null $a, int|float|string|null $b, int $scale = self::SCALE): int
    {
        return bccomp(self::round($a, $scale), self::round($b, $scale), $scale);
    }

    public static function sum(iterable $values, int $scale = self::SCALE): string
    {
        $total = '0';
        foreach ($values as $v) {
            $total = bcadd($total, self::normalize($v), self::CALC_SCALE);
        }

        return self::round($total, $scale);
    }

    /**
     * Format amount in Bangladeshi Taka comma style (e.g. ৳ 1,24,800.00).
     */
    public static function formatBd(int|float|string|null $value, bool $withSymbol = true): string
    {
        $rounded = self::round($value, 2);
        $negative = str_starts_with($rounded, '-');
        $abs = $negative ? substr($rounded, 1) : $rounded;

        [$intPart, $decPart] = array_pad(explode('.', $abs), 2, '00');

        if (strlen($intPart) > 3) {
            $lastThree = substr($intPart, -3);
            $rest = substr($intPart, 0, -3);
            $restFormatted = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
            $formatted = $restFormatted.','.$lastThree.'.'.$decPart;
        } else {
            $formatted = $intPart.'.'.$decPart;
        }

        $prefix = $negative ? '-' : '';

        return $withSymbol ? $prefix.'৳ '.$formatted : $prefix.$formatted;
    }

    /**
     * Convert monetary amount to Bangladeshi English words (Crore / Lakh / Thousand / Hundred).
     */
    public static function toWords(int|float|string|null $value): string
    {
        $rounded = self::round($value, 2);
        $abs = ltrim($rounded, '-');
        [$takaStr, $poishaStr] = array_pad(explode('.', $abs), 2, '00');

        $taka = (int) $takaStr;
        $poisha = (int) $poishaStr;

        if ($taka === 0 && $poisha === 0) {
            return 'Zero Taka Only';
        }

        $words = self::integerToBangladeshiWords($taka);
        $result = trim($words).' Taka';

        if ($poisha > 0) {
            $result .= ' and '.self::integerToBangladeshiWords($poisha).' Poisha';
        }

        return $result.' Only';
    }

    private static function integerToBangladeshiWords(int $number): string
    {
        if ($number === 0) {
            return 'Zero';
        }

        $ones = [
            '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
            'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
            'Seventeen', 'Eighteen', 'Nineteen',
        ];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        $twoDigits = static function (int $n) use ($ones, $tens): string {
            if ($n < 20) {
                return $ones[$n];
            }
            $t = intdiv($n, 10);
            $o = $n % 10;

            return trim($tens[$t].($o ? ' '.$ones[$o] : ''));
        };

        $parts = [];

        $crore = intdiv($number, 10000000);
        if ($crore > 0) {
            $parts[] = self::integerToBangladeshiWords($crore).' Crore';
            $number %= 10000000;
        }

        $lakh = intdiv($number, 100000);
        if ($lakh > 0) {
            $parts[] = $twoDigits($lakh).' Lakh';
            $number %= 100000;
        }

        $thousand = intdiv($number, 1000);
        if ($thousand > 0) {
            $parts[] = $twoDigits($thousand).' Thousand';
            $number %= 1000;
        }

        $hundred = intdiv($number, 100);
        if ($hundred > 0) {
            $parts[] = $ones[$hundred].' Hundred';
            $number %= 100;
        }

        if ($number > 0) {
            $parts[] = $twoDigits($number);
        }

        return implode(' ', $parts);
    }
}
