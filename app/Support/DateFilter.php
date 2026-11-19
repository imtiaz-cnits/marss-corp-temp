<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

/**
 * Shared business-date SQL parser and dashboard date range resolver.
 * Never leaks created_at or updated_at when explicit business dates exist.
 */
final class DateFilter
{
    /**
     * Resolve standard dashboard filter ranges.
     *
     * @return array{filterType: string, start: ?string, end: ?string, chartTitle: string}
     */
    public static function resolveRange(string $filterType = 'today', ?string $startDate = null, ?string $endDate = null): array
    {
        $today = Carbon::today()->toDateString();

        return match ($filterType) {
            'today' => [
                'filterType' => 'today',
                'start' => $today,
                'end' => $today,
                'chartTitle' => 'Sales & Profit Trend (Today)',
            ],
            'last_week' => [
                'filterType' => 'last_week',
                'start' => Carbon::today()->subDays(6)->toDateString(),
                'end' => $today,
                'chartTitle' => 'Sales & Profit Trend (Last 7 Days)',
            ],
            'this_month' => [
                'filterType' => 'this_month',
                'start' => Carbon::now()->startOfMonth()->toDateString(),
                'end' => $today,
                'chartTitle' => 'Sales & Profit Trend (This Month)',
            ],
            'last_month' => [
                'filterType' => 'last_month',
                'start' => Carbon::today()->subDays(29)->toDateString(),
                'end' => $today,
                'chartTitle' => 'Sales & Profit Trend (Last 30 Days)',
            ],
            'date_wise' => self::resolveCustomRange($startDate, $endDate, $today),
            default => [
                'filterType' => 'all_time',
                'start' => null,
                'end' => null,
                'chartTitle' => 'Sales & Profit Trend (Last 30 Days)',
            ],
        };
    }

    /**
     * @return array{filterType: string, start: string, end: string, chartTitle: string}
     */
    private static function resolveCustomRange(?string $startDate, ?string $endDate, string $fallback): array
    {
        $start = self::parseDateString($startDate) ?? $fallback;
        $end = self::parseDateString($endDate) ?? $start;

        if ($start > $end) {
            [$start, $end] = [$end, $start];
        }

        return [
            'filterType' => 'date_wise',
            'start' => $start,
            'end' => $end,
            'chartTitle' => 'Sales & Profit Trend ('.Carbon::parse($start)->format('d M').' - '.Carbon::parse($end)->format('d M Y').')',
        ];
    }

    public static function parseDateString(?string $value): ?string
    {
        if (! $value || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y'] as $fmt) {
            try {
                $dt = Carbon::createFromFormat($fmt, substr($value, 0, 10));
                if ($dt !== false) {
                    return $dt->toDateString();
                }
            } catch (\Throwable) {
            }
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Standardized MySQL expression for legacy varchar/date columns.
     */
    public static function dateSql(string $column): string
    {
        return "
            CASE
                WHEN {$column} REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}' THEN STR_TO_DATE(SUBSTRING({$column}, 1, 10), '%Y-%m-%d')
                WHEN {$column} REGEXP '^[0-9]{2}-[0-9]{2}-[0-9]{4}' THEN STR_TO_DATE(SUBSTRING({$column}, 1, 10), '%d-%m-%Y')
                WHEN {$column} REGEXP '^[0-9]{2}/[0-9]{2}/[0-9]{4}' THEN STR_TO_DATE(SUBSTRING({$column}, 1, 10), '%d/%m/%Y')
                ELSE NULL
            END
        ";
    }

    public static function applyDateFilter(EloquentBuilder|QueryBuilder $query, string $column, ?string $start, ?string $end): EloquentBuilder|QueryBuilder
    {
        if ($start && $end) {
            $query->whereBetween(DB::raw(self::dateSql($column)), [$start, $end]);
        } elseif ($start) {
            $query->where(DB::raw(self::dateSql($column)), '>=', $start);
        } elseif ($end) {
            $query->where(DB::raw(self::dateSql($column)), '<=', $end);
        }

        return $query;
    }

    public static function applyInvoiceDateFilter(EloquentBuilder|QueryBuilder $query, string $column = 'invoice_date', ?string $start = null, ?string $end = null, string $createdCol = 'created_at'): EloquentBuilder|QueryBuilder
    {
        $expr = "COALESCE({$column}, DATE({$createdCol}))";

        if ($start && $end) {
            $query->whereBetween(DB::raw($expr), [$start, $end]);
        } elseif ($start) {
            $query->where(DB::raw($expr), '>=', $start);
        } elseif ($end) {
            $query->where(DB::raw($expr), '<=', $end);
        }

        return $query;
    }
}
