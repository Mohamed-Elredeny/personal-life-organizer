<?php

namespace App\Filament\Widgets;

use App\Models\Sector;
use Filament\Widgets\Widget;

class SectorWorkload extends Widget
{
    protected static string $view = 'filament.widgets.sector-workload';
    protected static ?int $sort = 3;
    protected static ?string $pollingInterval = '60s';
    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 4,
    ];

    public function getViewData(): array
    {
        $sectors = Sector::query()
            ->where('is_archived', false)
            ->withCount([
                'tasks as open_tasks' => fn ($q) => $q->whereNotIn('status', ['done']),
                'tasks as done_tasks' => fn ($q) => $q->where('status', 'done'),
                'projects as active_projects' => fn ($q) => $q->whereIn('status', ['planning', 'active']),
            ])
            ->orderByDesc('open_tasks')
            ->get();

        $maxOpen = max($sectors->max('open_tasks') ?? 1, 1);

        return ['sectors' => $sectors, 'maxOpen' => $maxOpen];
    }
}
