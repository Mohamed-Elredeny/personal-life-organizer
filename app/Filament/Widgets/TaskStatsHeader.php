<?php

namespace App\Filament\Widgets;

use App\Models\Task;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TaskStatsHeader extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';
    protected function getColumns(): int { return 4; }

    protected function getStats(): array
    {
        $open = Task::whereNotIn('status', ['done'])->count();
        $today = Task::whereDate('due_date', today())->whereNotIn('status', ['done'])->count();
        $overdue = Task::whereDate('due_date', '<', today())->whereNotIn('status', ['done'])->count();
        $done7d = Task::where('status', 'done')->where('completed_at', '>=', now()->subDays(7))->count();

        return [
            Stat::make('Open', $open)->color('info')->icon('heroicon-o-inbox'),
            Stat::make('Due today', $today)->color($today > 0 ? 'warning' : 'gray')->icon('heroicon-o-calendar-days'),
            Stat::make('Overdue', $overdue)->color($overdue > 0 ? 'danger' : 'success')->icon('heroicon-o-exclamation-triangle'),
            Stat::make('Completed (7d)', $done7d)->color('success')->icon('heroicon-o-check-circle'),
        ];
    }
}
