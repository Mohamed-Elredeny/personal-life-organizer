<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Support\PeriodFilter;
use Filament\Widgets\ChartWidget;

class ExpensesByCategoryChart extends ChartWidget
{
    public function getHeading(): ?string { return __('Expenses by category'); }
    protected static ?int $sort = 4;
    protected static ?string $pollingInterval = '60s';
    protected int|string|array $columnSpan = [
        'md' => 1,
        'xl' => 4,
    ];

    protected function getData(): array
    {
        [$start, $end] = PeriodFilter::range();

        $rows = Transaction::query()
            ->selectRaw('category_id, SUM(amount) as total')
            ->where('type', 'expense')
            ->whereBetween('occurred_on', [$start, $end])
            ->groupBy('category_id')
            ->with('category')
            ->orderByDesc('total')
            ->get();

        if ($rows->isEmpty()) {
            return [
                'datasets' => [['data' => [1], 'backgroundColor' => ['#e2e8f0']]],
                'labels' => ['No data yet'],
            ];
        }

        return [
            'datasets' => [[
                'data' => $rows->map(fn ($r) => (float) $r->total)->all(),
                'backgroundColor' => $rows->map(fn ($r) => $r->category?->color ?? '#94a3b8')->all(),
                'borderWidth' => 2,
                'borderColor' => '#ffffff',
            ]],
            'labels' => $rows->map(fn ($r) => $r->category?->name ?? 'Uncategorized')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'bottom', 'labels' => ['padding' => 16]],
            ],
            'cutout' => '70%',
        ];
    }
}
