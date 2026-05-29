<?php

namespace App\Filament\Widgets;

use App\Models\Goal;
use App\Models\Task;
use App\Models\Transaction;
use App\Support\Money;
use App\Support\PeriodFilter;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 0;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $pollingInterval = '30s';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        [$start, $end] = PeriodFilter::range();
        [$prevStart, $prevEnd] = PeriodFilter::previousRange();
        $label = PeriodFilter::label();

        // Tasks completed
        $tasksDone = Task::where('status', 'done')
            ->whereBetween('completed_at', [$start, $end])->count();
        $tasksDonePrev = Task::where('status', 'done')
            ->whereBetween('completed_at', [$prevStart, $prevEnd])->count();
        $tasksDelta = PeriodFilter::delta($tasksDone, $tasksDonePrev);

        // Tasks due today (independent of period)
        $tasksToday = Task::whereDate('due_date', today())
            ->whereNotIn('status', ['done'])->count();
        $tasksOverdue = Task::whereDate('due_date', '<', today())
            ->whereNotIn('status', ['done'])->count();

        // Income vs expenses for the period — primary currency only
        $currency = auth()->user()->default_currency ?? 'SAR';
        $base = fn () => Transaction::where('currency', $currency);

        $income   = (float) $base()->where('type', 'income')->whereBetween('occurred_on', [$start, $end])->sum('amount');
        $expenses = (float) $base()->where('type', 'expense')->whereBetween('occurred_on', [$start, $end])->sum('amount');
        $net = $income - $expenses;

        // Goal progress
        $activeGoals = Goal::whereIn('status', ['in_progress', 'not_started'])->count();
        $avgGoalProgress = (int) (Goal::whereIn('status', ['in_progress', 'not_started'])->avg('progress') ?? 0);

        return [
            Stat::make(__('Tasks completed'), $tasksDone)
                ->description($this->trend($tasksDelta) . ' ' . __('vs previous :period', ['period' => $label]))
                ->descriptionIcon($this->trendIcon($tasksDelta['direction']))
                ->color($tasksDelta['direction'] === 'up' ? 'success' : ($tasksDelta['direction'] === 'down' ? 'danger' : 'gray'))
                ->chart($this->taskChart()),

            Stat::make(__('Open today / overdue'), $tasksToday . ' / ' . $tasksOverdue)
                ->description($tasksOverdue > 0 ? __(':n task(s) past due', ['n' => $tasksOverdue]) : __('All on track'))
                ->descriptionIcon($tasksOverdue > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($tasksOverdue > 0 ? 'danger' : 'success'),

            Stat::make(__('Net :period', ['period' => $label]), Money::format($net, $currency, 0))
                ->description(__('In :in · Out :out', ['in' => Money::format($income, $currency, 0), 'out' => Money::format($expenses, $currency, 0)]))
                ->descriptionIcon($net >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($net >= 0 ? 'success' : 'danger')
                ->chart($this->cashflowChart($currency)),

            Stat::make(__('Active goals'), $activeGoals)
                ->description(__('Avg progress') . ' · ' . $avgGoalProgress . '%')
                ->descriptionIcon('heroicon-m-flag')
                ->color($avgGoalProgress >= 60 ? 'success' : ($avgGoalProgress >= 30 ? 'info' : 'gray')),
        ];
    }

    private function trend(array $delta): string
    {
        if ($delta['percent'] === null) {
            return $delta['direction'] === 'up' ? 'New' : 'No change';
        }
        $sign = $delta['percent'] > 0 ? '+' : '';
        return $sign . $delta['percent'] . '%';
    }

    private function trendIcon(string $dir): string
    {
        return match ($dir) {
            'up' => 'heroicon-m-arrow-trending-up',
            'down' => 'heroicon-m-arrow-trending-down',
            default => 'heroicon-m-minus',
        };
    }

    private function taskChart(): array
    {
        $data = [];
        for ($i = 13; $i >= 0; $i--) {
            $data[] = Task::where('status', 'done')
                ->whereDate('completed_at', today()->subDays($i))->count();
        }
        return $data;
    }

    private function cashflowChart(string $currency): array
    {
        $data = [];
        for ($i = 13; $i >= 0; $i--) {
            $day = today()->subDays($i);
            $in  = (float) Transaction::where('currency', $currency)->where('type', 'income')->whereDate('occurred_on', $day)->sum('amount');
            $out = (float) Transaction::where('currency', $currency)->where('type', 'expense')->whereDate('occurred_on', $day)->sum('amount');
            $data[] = $in - $out;
        }
        return $data;
    }
}
