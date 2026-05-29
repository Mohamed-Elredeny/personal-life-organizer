<?php

namespace App\Filament\Widgets;

use App\Models\Goal;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GoalStatsHeader extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';
    protected function getColumns(): int { return 4; }

    protected function getStats(): array
    {
        $active = Goal::whereIn('status', ['in_progress', 'not_started'])->count();
        $done = Goal::where('status', 'completed')->count();
        $avg = (int) (Goal::whereIn('status', ['in_progress', 'not_started'])->avg('progress') ?? 0);
        $dueSoon = Goal::whereIn('status', ['in_progress', 'not_started'])
            ->whereNotNull('target_date')
            ->whereBetween('target_date', [today(), today()->addDays(30)])->count();

        return [
            Stat::make('Active goals', $active)->color('info')->icon('heroicon-o-flag'),
            Stat::make('Completed', $done)->color('success')->icon('heroicon-o-trophy'),
            Stat::make('Avg progress', $avg . '%')
                ->color($avg >= 60 ? 'success' : ($avg >= 30 ? 'info' : 'gray'))
                ->icon('heroicon-o-chart-bar'),
            Stat::make('Due in 30 days', $dueSoon)
                ->color($dueSoon > 0 ? 'warning' : 'gray')
                ->icon('heroicon-o-calendar'),
        ];
    }
}
