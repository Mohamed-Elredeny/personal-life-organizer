<?php

namespace App\Filament\Resources\ScholarshipResource\Pages;

use App\Filament\Resources\ScholarshipResource;
use App\Filament\Widgets\PhdStatsHeader;
use App\Models\Scholarship;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListScholarships extends ListRecords
{
    protected static string $resource = ScholarshipResource::class;

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
            'all' => Tab::make(__('All'))->badge(Scholarship::count()),
            'shortlist' => Tab::make(__('Shortlist'))
                ->modifyQueryUsing(fn (Builder $q) => $q->whereIn('status', ['interested', 'shortlisted']))
                ->badge(Scholarship::whereIn('status', ['interested', 'shortlisted'])->count()),
            'in_flight' => Tab::make(__('In flight'))
                ->modifyQueryUsing(fn (Builder $q) => $q->whereIn('status', ['applied', 'interview']))
                ->badge(Scholarship::whereIn('status', ['applied', 'interview'])->count())
                ->badgeColor('info'),
            'won' => Tab::make(__('Won'))
                ->modifyQueryUsing(fn (Builder $q) => $q->whereIn('status', ['accepted', 'enrolled']))
                ->badge(Scholarship::whereIn('status', ['accepted', 'enrolled'])->count())
                ->badgeColor('success'),
            'closed' => Tab::make(__('Closed'))
                ->modifyQueryUsing(fn (Builder $q) => $q->whereIn('status', ['rejected', 'withdrawn']))
                ->badge(Scholarship::whereIn('status', ['rejected', 'withdrawn'])->count())
                ->badgeColor('danger'),
        ];
    }
}
