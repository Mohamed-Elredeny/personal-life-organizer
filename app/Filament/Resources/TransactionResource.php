<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string { return __('Finance'); }
    public static function getNavigationLabel(): string { return __('Transactions'); }
    public static function getModelLabel(): string { return __('Transaction'); }
    public static function getPluralModelLabel(): string { return __('Transactions'); }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->columns(2)->schema([
                Forms\Components\Select::make('type')
                    ->options(['income' => 'Income', 'expense' => 'Expense'])
                    ->default('expense')->required()->native(false)->live(),
                Forms\Components\TextInput::make('amount')
                    ->required()->numeric()
                    ->prefix(fn ($livewire) => \App\Support\Money::symbol($livewire->data['currency'] ?? 'SAR')),
                Forms\Components\Select::make('currency')
                    ->options(\App\Support\Money::shortOptions())
                    ->default(fn () => auth()->user()->default_currency ?? 'SAR')
                    ->required()->native(false)->live(),
                Forms\Components\DatePicker::make('occurred_on')->default(now())->required()->native(false),
                Forms\Components\Select::make('category_id')
                    ->relationship(
                        name: 'category',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (\Illuminate\Database\Eloquent\Builder $query) {
                            $type = request()->input('data.type') ?? 'expense';
                            $query->where('type', $type);
                        },
                    )
                    ->searchable()->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')->required(),
                        Forms\Components\Hidden::make('type')->default(fn ($livewire) => $livewire->data['type'] ?? 'expense'),
                        Forms\Components\ColorPicker::make('color')->default('#10b981'),
                    ])
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('description')->maxLength(255)->columnSpanFull(),
                Forms\Components\TextInput::make('reference')->maxLength(255),
                Forms\Components\Toggle::make('is_shared')->label('Share with partner'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('occurred_on')->date()->sortable(),
                Tables\Columns\TextColumn::make('description')->searchable()->wrap()->weight('semibold')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('category.name')->badge(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state) => $state === 'income' ? 'success' : 'danger')
                    ->icon(fn (string $state) => $state === 'income' ? 'heroicon-m-arrow-up' : 'heroicon-m-arrow-down'),
                Tables\Columns\TextColumn::make('amount')
                    ->formatStateUsing(fn ($state, $record) => \App\Support\Money::format($state, $record->currency, 2))
                    ->color(fn ($record) => $record->type === 'income' ? 'success' : 'danger')
                    ->weight('bold')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_shared')->boolean()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->options(['income' => 'Income', 'expense' => 'Expense']),
                Tables\Filters\SelectFilter::make('category')->relationship('category', 'name'),
                Tables\Filters\Filter::make('this_month')
                    ->label('This month')
                    ->query(fn ($q) => $q->whereMonth('occurred_on', now()->month)->whereYear('occurred_on', now()->year)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->defaultSort('occurred_on', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
