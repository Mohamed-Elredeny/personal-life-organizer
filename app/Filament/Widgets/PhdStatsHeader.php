<?php

namespace App\Filament\Widgets;

use App\Models\Professor;
use App\Models\Scholarship;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PhdStatsHeader extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';
    protected function getColumns(): int { return 4; }

    protected function getStats(): array
    {
        $shortlisted = Scholarship::whereIn('status', ['interested', 'shortlisted'])->count();
        $applied     = Scholarship::whereIn('status', ['applied', 'interview'])->count();
        $accepted    = Scholarship::whereIn('status', ['accepted', 'enrolled'])->count();

        $contacted   = Professor::whereIn('status', ['contacted', 'replied', 'meeting_scheduled'])->count();
        $positive    = Professor::where('status', 'positive')->count();
        $followToday = Professor::whereDate('next_follow_up_at', '<=', today())
            ->whereNotIn('status', ['closed', 'negative', 'positive'])->count();

        $upcomingDeadline = Scholarship::whereBetween('deadline', [today(), today()->addDays(30)])
            ->whereNotIn('status', ['rejected', 'withdrawn', 'enrolled'])->count();

        return [
            Stat::make(__('Scholarships shortlisted'), $shortlisted)
                ->description($applied . ' ' . __('applied') . ' · ' . $accepted . ' ' . __('accepted'))
                ->color('info')
                ->icon('heroicon-o-trophy'),
            Stat::make(__('Professors in flight'), $contacted)
                ->description($positive . ' ' . __('positive replies'))
                ->color('primary')
                ->icon('heroicon-o-user-group'),
            Stat::make(__('Follow-ups due'), $followToday)
                ->color($followToday > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-bell-alert'),
            Stat::make(__('Deadlines · 30d'), $upcomingDeadline)
                ->color($upcomingDeadline > 0 ? 'danger' : 'gray')
                ->icon('heroicon-o-calendar-days'),
        ];
    }
}
