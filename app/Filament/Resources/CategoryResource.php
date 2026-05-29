<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?int $navigationSort = 3;
    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string { return __('Finance'); }
    public static function getNavigationLabel(): string { return __('Categories'); }
    public static function getModelLabel(): string { return __('Category'); }
    public static function getPluralModelLabel(): string { return __('Categories'); }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->columns(2)->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\Select::make('type')
                    ->options(['income' => 'Income', 'expense' => 'Expense'])
                    ->default('expense')->required()->native(false),
                Forms\Components\ColorPicker::make('color')->default('#10b981'),
                Forms\Components\Select::make('icon')
                    ->options([
                        'heroicon-o-home' => 'Home',
                        'heroicon-o-shopping-cart' => 'Shopping',
                        'heroicon-o-truck' => 'Transport',
                        'heroicon-o-cake' => 'Food',
                        'heroicon-o-academic-cap' => 'Education',
                        'heroicon-o-heart' => 'Health',
                        'heroicon-o-gift' => 'Gifts',
                        'heroicon-o-banknotes' => 'Salary',
                        'heroicon-o-briefcase' => 'Business',
                    ])->searchable(),
                Forms\Components\Toggle::make('is_shared')->label('Share with partner')->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ColorColumn::make('color'),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable()->weight('semibold'),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state) => $state === 'income' ? 'success' : 'danger'),
                Tables\Columns\IconColumn::make('is_shared')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->options(['income' => 'Income', 'expense' => 'Expense']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
