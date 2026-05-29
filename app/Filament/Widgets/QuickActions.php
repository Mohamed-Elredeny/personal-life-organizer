<?php

namespace App\Filament\Widgets;

use App\Support\PeriodFilter;
use Filament\Widgets\Widget;

class QuickActions extends Widget
{
    protected static string $view = 'filament.widgets.quick-actions';
    protected static ?int $sort = -1;
    protected int|string|array $columnSpan = 'full';

    public string $period;

    public function mount(): void
    {
        $this->period = PeriodFilter::current();
    }

    public function setPeriod(string $period): void
    {
        PeriodFilter::set($period);
        $this->period = $period;
        $this->dispatch('$refresh');
    }

    public function getViewData(): array
    {
        return [
            'period' => $this->period,
            'periods' => PeriodFilter::options(),
        ];
    }
}
