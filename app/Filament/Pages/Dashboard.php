<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\BalancesByCurrency;
use App\Filament\Widgets\ExpensesByCategoryChart;
use App\Filament\Widgets\FinanceChart;
use App\Filament\Widgets\GoalProgressChart;
use App\Filament\Widgets\QuickActions;
use App\Filament\Widgets\RecentActivity;
use App\Filament\Widgets\SectorWorkload;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\UpcomingAnniversariesList;
use App\Filament\Widgets\UpcomingTasksTable;
use Filament\Pages\Dashboard as BasePage;
use Filament\Support\Enums\MaxWidth;

class Dashboard extends BasePage
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    public static function getNavigationLabel(): string { return __('Overview'); }
    public function getTitle(): string { return __('Overview'); }
    protected static ?int $navigationSort = -2;

    public ?string $period = 'month';

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            QuickActions::class,
            StatsOverview::class,
        ];
    }

    public function getWidgets(): array
    {
        return [
            BalancesByCurrency::class,
            FinanceChart::class,
            SectorWorkload::class,
            ExpensesByCategoryChart::class,
            GoalProgressChart::class,
            UpcomingTasksTable::class,
            UpcomingAnniversariesList::class,
            RecentActivity::class,
        ];
    }

    public function getColumns(): int|string|array
    {
        return [
            'md' => 2,
            'xl' => 12,
        ];
    }

    public function getWidgetData(): array
    {
        return ['period' => $this->period];
    }

    public function setPeriod(string $period): void
    {
        $this->period = $period;
        $this->dispatch('period-changed', period: $period);
    }

    public function getHeading(): string
    {
        $hour = now()->hour;
        $greeting = match (true) {
            $hour < 12 => __('Good morning'),
            $hour < 18 => __('Good afternoon'),
            default => __('Good evening'),
        };
        $name = explode(' ', auth()->user()->name)[0] ?? '';
        return "{$greeting}, {$name}";
    }

    public function getSubheading(): ?string
    {
        return now()->format('l, F j · Y');
    }
}
