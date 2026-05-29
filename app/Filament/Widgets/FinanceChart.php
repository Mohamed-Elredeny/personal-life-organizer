<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Support\PeriodFilter;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class FinanceChart extends ChartWidget
{
    public function getHeading(): ?string { return __('Income vs Expenses'); }
    protected static ?int $sort = 2;
    protected static ?string $pollingInterval = '60s';
    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 8,
    ];

    public ?string $filter = 'month';

    public function mount(): void
    {
        $this->filter = PeriodFilter::current();
    }

    protected function getFilters(): ?array
    {
        return PeriodFilter::options();
    }

    public function updatedFilter(): void
    {
        PeriodFilter::set($this->filter);
    }

    protected function getData(): array
    {
        $period = $this->filter ?? PeriodFilter::current();
        [$buckets, $labels] = $this->bucketsForPeriod($period);

        $income = [];
        $expense = [];
        foreach ($buckets as [$start, $end]) {
            $income[]  = (float) Transaction::where('type', 'income')->whereBetween('occurred_on', [$start, $end])->sum('amount');
            $expense[] = (float) Transaction::where('type', 'expense')->whereBetween('occurred_on', [$start, $end])->sum('amount');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Income',
                    'data' => $income,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.18)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 3,
                    'pointHoverRadius' => 6,
                ],
                [
                    'label' => 'Expenses',
                    'data' => $expense,
                    'backgroundColor' => 'rgba(244, 63, 94, 0.18)',
                    'borderColor' => 'rgb(244, 63, 94)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 3,
                    'pointHoverRadius' => 6,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'bottom'],
            ],
            'scales' => [
                'y' => ['beginAtZero' => true],
            ],
            'interaction' => ['intersect' => false, 'mode' => 'index'],
        ];
    }

    private function bucketsForPeriod(string $period): array
    {
        $now = now();
        return match ($period) {
            'week' => [
                collect(range(0, 6))->map(fn ($i) => [
                    $now->copy()->startOfWeek()->addDays($i)->startOfDay(),
                    $now->copy()->startOfWeek()->addDays($i)->endOfDay(),
                ])->all(),
                collect(range(0, 6))->map(fn ($i) => $now->copy()->startOfWeek()->addDays($i)->format('D'))->all(),
            ],
            'quarter' => [
                collect(range(0, 2))->map(fn ($i) => [
                    $now->copy()->firstOfQuarter()->addMonths($i)->startOfMonth(),
                    $now->copy()->firstOfQuarter()->addMonths($i)->endOfMonth(),
                ])->all(),
                collect(range(0, 2))->map(fn ($i) => $now->copy()->firstOfQuarter()->addMonths($i)->format('M'))->all(),
            ],
            'year' => [
                collect(range(0, 11))->map(fn ($i) => [
                    $now->copy()->startOfYear()->addMonths($i)->startOfMonth(),
                    $now->copy()->startOfYear()->addMonths($i)->endOfMonth(),
                ])->all(),
                collect(range(0, 11))->map(fn ($i) => Carbon::create($now->year, $i + 1, 1)->format('M'))->all(),
            ],
            default => [
                collect(range(1, $now->daysInMonth))->map(fn ($d) => [
                    $now->copy()->startOfMonth()->addDays($d - 1)->startOfDay(),
                    $now->copy()->startOfMonth()->addDays($d - 1)->endOfDay(),
                ])->all(),
                collect(range(1, $now->daysInMonth))->map(fn ($d) => (string) $d)->all(),
            ],
        };
    }
}
