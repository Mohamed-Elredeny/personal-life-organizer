<?php

namespace App\Filament\Widgets;

use App\Models\Goal;
use Filament\Widgets\ChartWidget;

class GoalProgressChart extends ChartWidget
{
    public function getHeading(): ?string { return __('Goal progress by category'); }
    protected static ?int $sort = 5;
    protected static ?string $pollingInterval = '60s';
    protected int|string|array $columnSpan = [
        'md' => 1,
        'xl' => 4,
    ];

    protected function getData(): array
    {
        $rows = Goal::query()
            ->selectRaw('category, AVG(progress) as avg_progress, COUNT(*) as cnt')
            ->whereNotIn('status', ['abandoned'])
            ->groupBy('category')
            ->get();

        if ($rows->isEmpty()) {
            return [
                'datasets' => [['label' => 'Avg progress', 'data' => [], 'backgroundColor' => []]],
                'labels' => [],
            ];
        }

        $colors = [
            'work' => '#0ea5e9',
            'finance' => '#10b981',
            'marriage' => '#f43f5e',
            'personal' => '#94a3b8',
            'health' => '#f59e0b',
            'learning' => '#6366f1',
        ];

        return [
            'datasets' => [[
                'label' => 'Avg progress (%)',
                'data' => $rows->pluck('avg_progress')->map(fn ($v) => (int) $v)->all(),
                'backgroundColor' => $rows->pluck('category')->map(fn ($c) => $colors[$c] ?? '#94a3b8')->all(),
                'borderRadius' => 8,
                'borderSkipped' => false,
            ]],
            'labels' => $rows->pluck('category')->map(fn ($c) => ucfirst($c))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            'scales' => [
                'y' => ['min' => 0, 'max' => 100, 'ticks' => ['callback' => null]],
                'x' => ['grid' => ['display' => false]],
            ],
        ];
    }
}
