<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BudgetResource\Pages;
use App\Models\Budget;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BudgetResource extends Resource
{
    protected static ?string $model = Budget::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string { return __('Finance'); }
    public static function getNavigationLabel(): string { return __('Budgets'); }
    public static function getModelLabel(): string { return __('Budget'); }
    public static function getPluralModelLabel(): string { return __('Budgets'); }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->columns(2)->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255)->columnSpanFull(),
                Forms\Components\Select::make('category_id')
                    ->relationship(
                        name: 'category',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('type', 'expense'),
                    )
                    ->required()->searchable()->preload(),
                Forms\Components\Select::make('period')
                    ->options([
                        'weekly' => 'Weekly', 'monthly' => 'Monthly',
                        'quarterly' => 'Quarterly', 'yearly' => 'Yearly',
                    ])
                    ->default('monthly')->required()->native(false),
                Forms\Components\Select::make('currency')
                    ->options(\App\Support\Money::shortOptions())
                    ->default(fn () => auth()->user()->default_currency ?? 'SAR')
                    ->required()->native(false)->live(),
                Forms\Components\TextInput::make('amount')->required()->numeric()
                    ->prefix(fn ($livewire) => \App\Support\Money::symbol($livewire->data['currency'] ?? 'SAR')),
                Forms\Components\DatePicker::make('starts_on')->default(now()->startOfMonth())->required()->native(false),
                Forms\Components\Toggle::make('is_shared')->label('Share with partner'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->weight('semibold'),
                Tables\Columns\TextColumn::make('category.name')->badge(),
                Tables\Columns\TextColumn::make('period')->badge(),
                Tables\Columns\TextColumn::make('amount')
                    ->formatStateUsing(fn ($state, $record) => \App\Support\Money::format($state, $record->currency, 0))
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('spent')
                    ->label(__('Spent (current period)'))
                    ->state(function (Budget $record) {
                        return Transaction::query()
                            ->where('category_id', $record->category_id)
                            ->where('type', 'expense')
                            ->whereBetween('occurred_on', self::currentPeriodRange($record))
                            ->sum('amount');
                    })
                    ->formatStateUsing(fn ($state, $record) => \App\Support\Money::format((float) $state, $record->currency, 0))
                    ->color(fn ($state, $record) => $state >= $record->amount ? 'danger' : ($state >= $record->amount * 0.8 ? 'warning' : 'success')),
                Tables\Columns\TextColumn::make('starts_on')->date()->toggleable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->defaultSort('starts_on', 'desc');
    }

    protected static function currentPeriodRange(Budget $b): array
    {
        $now = now();
        return match ($b->period) {
            'weekly' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'monthly' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'quarterly' => [$now->copy()->firstOfQuarter(), $now->copy()->lastOfQuarter()],
            'yearly' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
        };
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBudgets::route('/'),
            'create' => Pages\CreateBudget::route('/create'),
            'edit' => Pages\EditBudget::route('/{record}/edit'),
        ];
    }
}
