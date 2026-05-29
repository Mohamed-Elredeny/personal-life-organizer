<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

class PeriodFilter
{
    public const KEY = 'dashboard.period';
    public const DEFAULT = 'month';

    public static function options(): array
    {
        return [
            'week' => __('This week'),
            'month' => __('This month'),
            'quarter' => __('This quarter'),
            'year' => __('This year'),
        ];
    }

    private const KEYS = ['week', 'month', 'quarter', 'year'];

    public static function current(): string
    {
        $value = Session::get(self::KEY, self::DEFAULT);
        return in_array($value, self::KEYS, true) ? $value : self::DEFAULT;
    }

    public static function set(string $value): void
    {
        if (in_array($value, self::KEYS, true)) {
            Session::put(self::KEY, $value);
        }
    }

    public static function range(?string $period = null): array
    {
        $period ??= self::current();
        $now = now();
        return match ($period) {
            'week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'quarter' => [$now->copy()->firstOfQuarter(), $now->copy()->lastOfQuarter()],
            'year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        };
    }

    public static function previousRange(?string $period = null): array
    {
        $period ??= self::current();
        $now = now();
        return match ($period) {
            'week' => [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()],
            'quarter' => [$now->copy()->subQuarter()->firstOfQuarter(), $now->copy()->subQuarter()->lastOfQuarter()],
            'year' => [$now->copy()->subYear()->startOfYear(), $now->copy()->subYear()->endOfYear()],
            default => [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()],
        };
    }

    public static function label(?string $period = null): string
    {
        $period ??= self::current();
        return self::options()[$period] ?? __('This month');
    }

    public static function delta(float $current, float $previous): array
    {
        if ($previous == 0.0) {
            return ['percent' => null, 'direction' => $current > 0 ? 'up' : 'flat'];
        }
        $pct = (($current - $previous) / abs($previous)) * 100;
        return [
            'percent' => round($pct, 1),
            'direction' => $pct > 0.5 ? 'up' : ($pct < -0.5 ? 'down' : 'flat'),
        ];
    }
}
