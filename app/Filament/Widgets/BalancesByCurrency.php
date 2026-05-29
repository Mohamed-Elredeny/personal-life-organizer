<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Support\Money;
use App\Support\PeriodFilter;
use Filament\Widgets\Widget;

class BalancesByCurrency extends Widget
{
    protected static string $view = 'filament.widgets.balances-by-currency';
    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = '30s';
    protected int|string|array $columnSpan = 'full';

    public function getViewData(): array
    {
        [$start, $end] = PeriodFilter::range();
        [$pStart, $pEnd] = PeriodFilter::previousRange();

        $rows = [];
        foreach (array_keys(Money::CURRENCIES) as $code) {
            $in  = (float) Transaction::where('currency', $code)->where('type', 'income')
                ->whereBetween('occurred_on', [$start, $end])->sum('amount');
            $out = (float) Transaction::where('currency', $code)->where('type', 'expense')
                ->whereBetween('occurred_on', [$start, $end])->sum('amount');

            $inPrev = (float) Transaction::where('currency', $code)->where('type', 'income')
                ->whereBetween('occurred_on', [$pStart, $pEnd])->sum('amount');
            $outPrev = (float) Transaction::where('currency', $code)->where('type', 'expense')
                ->whereBetween('occurred_on', [$pStart, $pEnd])->sum('amount');

            $rows[] = [
                'code'     => $code,
                'symbol'   => Money::symbol($code),
                'in'       => Money::format($in,  $code, 0),
                'out'      => Money::format($out, $code, 0),
                'net'      => Money::format($in - $out, $code, 0),
                'net_raw'  => $in - $out,
                'in_delta'  => PeriodFilter::delta($in,  $inPrev),
                'out_delta' => PeriodFilter::delta($out, $outPrev),
                'has_data' => ($in + $out + $inPrev + $outPrev) > 0,
            ];
        }

        return [
            'rows' => $rows,
            'period_label' => PeriodFilter::label(),
        ];
    }
}
