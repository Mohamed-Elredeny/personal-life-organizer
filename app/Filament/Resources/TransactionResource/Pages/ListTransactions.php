<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Filament\Widgets\ExpensesByCategoryChart;
use App\Filament\Widgets\FinanceChart;
use App\Filament\Widgets\FinanceStatsHeader;
use App\Models\Transaction;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderWidgets(): array
    {
        return [FinanceStatsHeader::class, FinanceChart::class];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return ['default' => 1, 'lg' => 3];
    }

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->icon('heroicon-m-plus')];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')->badge(Transaction::count()),
            'income' => Tab::make('Income')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('type', 'income'))
                ->badge(Transaction::where('type', 'income')->count())
                ->badgeColor('success'),
            'expenses' => Tab::make('Expenses')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('type', 'expense'))
                ->badge(Transaction::where('type', 'expense')->count())
                ->badgeColor('danger'),
            'this_month' => Tab::make('This month')
                ->modifyQueryUsing(fn (Builder $q) => $q->whereMonth('occurred_on', now()->month)->whereYear('occurred_on', now()->year))
                ->badge(Transaction::whereMonth('occurred_on', now()->month)->whereYear('occurred_on', now()->year)->count())
                ->badgeColor('info'),
        ];
    }
}
