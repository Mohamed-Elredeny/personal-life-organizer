<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Support\Money;
use App\Support\PeriodFilter;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinanceStatsHeader extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';
    protected function getColumns(): int { return 4; }

    protected function getStats(): array
    {
        [$start, $end] = PeriodFilter::range();
        [$pStart, $pEnd] = PeriodFilter::previousRange();
        $label = PeriodFilter::label();
        $currency = auth()->user()->default_currency ?? 'SAR';

        $base = fn () => Transaction::where('currency', $currency);

        $in  = (float) $base()->where('type', 'income')->whereBetween('occurred_on', [$start, $end])->sum('amount');
        $out = (float) $base()->where('type', 'expense')->whereBetween('occurred_on', [$start, $end])->sum('amount');
        $net = $in - $out;
        $sr  = ($in > 0) ? round((($in - $out) / $in) * 100, 1) : 0;

        $inPrev  = (float) $base()->where('type', 'income')->whereBetween('occurred_on', [$pStart, $pEnd])->sum('amount');
        $outPrev = (float) $base()->where('type', 'expense')->whereBetween('occurred_on', [$pStart, $pEnd])->sum('amount');

        $dIn  = PeriodFilter::delta($in,  $inPrev);
        $dOut = PeriodFilter::delta($out, $outPrev);

        $fmtDelta = fn ($d) => $d['percent'] !== null
            ? (($d['percent'] > 0 ? '+' : '') . $d['percent'] . '%')
            : __('New');

        return [
            Stat::make(__('Income') . ' · ' . $label, Money::format($in, $currency, 0))
                ->description($fmtDelta($dIn) . ' ' . __('vs previous'))
                ->descriptionIcon($dIn['direction'] === 'up' ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('success'),
            Stat::make(__('Expenses') . ' · ' . $label, Money::format($out, $currency, 0))
                ->description($fmtDelta($dOut) . ' ' . __('vs previous'))
                ->descriptionIcon($dOut['direction'] === 'up' ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($dOut['direction'] === 'up' ? 'danger' : 'success'),
            Stat::make(__('Net'), Money::format($net, $currency, 0))
                ->color($net >= 0 ? 'success' : 'danger')
                ->icon($net >= 0 ? 'heroicon-o-banknotes' : 'heroicon-o-exclamation-circle'),
            Stat::make(__('Savings rate'), $sr . '%')
                ->color($sr >= 20 ? 'success' : ($sr >= 0 ? 'info' : 'danger'))
                ->icon('heroicon-o-chart-bar'),
        ];
    }
}
