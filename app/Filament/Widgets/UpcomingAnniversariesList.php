<?php

namespace App\Filament\Widgets;

use App\Models\Anniversary;
use Filament\Widgets\Widget;

class UpcomingAnniversariesList extends Widget
{
    protected static string $view = 'filament.widgets.upcoming-anniversaries-list';
    protected static ?int $sort = 8;
    protected static ?string $pollingInterval = '120s';
    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 6,
    ];

    public function getViewData(): array
    {
        $items = Anniversary::all()
            ->sortBy(fn ($a) => $a->daysUntilNext())
            ->take(6)
            ->values();

        return ['items' => $items];
    }
}
