<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use App\Filament\Widgets\TaskStatsHeader;
use App\Models\Task;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderWidgets(): array
    {
        return [TaskStatsHeader::class];
    }

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->icon('heroicon-m-plus')];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(Task::count()),
            'today' => Tab::make('Today')
                ->modifyQueryUsing(fn (Builder $q) => $q->whereDate('due_date', today())->whereNotIn('status', ['done']))
                ->badge(Task::whereDate('due_date', today())->whereNotIn('status', ['done'])->count())
                ->badgeColor('warning'),
            'overdue' => Tab::make('Overdue')
                ->modifyQueryUsing(fn (Builder $q) => $q->whereDate('due_date', '<', today())->whereNotIn('status', ['done']))
                ->badge(Task::whereDate('due_date', '<', today())->whereNotIn('status', ['done'])->count())
                ->badgeColor('danger'),
            'this_week' => Tab::make('This week')
                ->modifyQueryUsing(fn (Builder $q) => $q->whereBetween('due_date', [now()->startOfWeek(), now()->endOfWeek()])->whereNotIn('status', ['done']))
                ->badge(Task::whereBetween('due_date', [now()->startOfWeek(), now()->endOfWeek()])->whereNotIn('status', ['done'])->count())
                ->badgeColor('info'),
            'done' => Tab::make('Done')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'done'))
                ->badge(Task::where('status', 'done')->count())
                ->badgeColor('success'),
        ];
    }
}
