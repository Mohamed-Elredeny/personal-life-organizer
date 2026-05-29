<?php

namespace App\Filament\Resources\GoalResource\Pages;

use App\Filament\Resources\GoalResource;
use App\Filament\Widgets\GoalStatsHeader;
use App\Models\Goal;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListGoals extends ListRecords
{
    protected static string $resource = GoalResource::class;

    protected function getHeaderWidgets(): array
    {
        return [GoalStatsHeader::class];
    }

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->icon('heroicon-m-plus')];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')->badge(Goal::count()),
            'active' => Tab::make('Active')
                ->modifyQueryUsing(fn (Builder $q) => $q->whereIn('status', ['in_progress', 'not_started']))
                ->badge(Goal::whereIn('status', ['in_progress', 'not_started'])->count())
                ->badgeColor('info'),
            'completed' => Tab::make('Completed')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'completed'))
                ->badge(Goal::where('status', 'completed')->count())
                ->badgeColor('success'),
            'quarterly' => Tab::make('This quarter')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('horizon', 'quarterly'))
                ->badge(Goal::where('horizon', 'quarterly')->count()),
        ];
    }
}
