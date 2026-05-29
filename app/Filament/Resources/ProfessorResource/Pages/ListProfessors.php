<?php

namespace App\Filament\Resources\ProfessorResource\Pages;

use App\Filament\Resources\ProfessorResource;
use App\Filament\Widgets\PhdStatsHeader;
use App\Models\Professor;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListProfessors extends ListRecords
{
    protected static string $resource = ProfessorResource::class;

    protected function getHeaderWidgets(): array
    {
        return [PhdStatsHeader::class];
    }

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->icon('heroicon-m-plus')];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('All'))->badge(Professor::count()),
            'planned' => Tab::make(__('Planned'))
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'planned'))
                ->badge(Professor::where('status', 'planned')->count()),
            'in_flight' => Tab::make(__('In flight'))
                ->modifyQueryUsing(fn (Builder $q) => $q->whereIn('status', ['contacted', 'replied', 'meeting_scheduled']))
                ->badge(Professor::whereIn('status', ['contacted', 'replied', 'meeting_scheduled'])->count())
                ->badgeColor('info'),
            'positive' => Tab::make(__('Positive'))
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'positive'))
                ->badge(Professor::where('status', 'positive')->count())
                ->badgeColor('success'),
            'follow_up' => Tab::make(__('Follow-up due'))
                ->modifyQueryUsing(fn (Builder $q) => $q->whereDate('next_follow_up_at', '<=', today())
                    ->whereNotIn('status', ['closed', 'negative', 'positive']))
                ->badge(Professor::whereDate('next_follow_up_at', '<=', today())
                    ->whereNotIn('status', ['closed', 'negative', 'positive'])->count())
                ->badgeColor('warning'),
        ];
    }
}
