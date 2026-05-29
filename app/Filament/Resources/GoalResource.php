<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GoalResource\Pages;
use App\Filament\Resources\GoalResource\RelationManagers\MilestonesRelationManager;
use App\Filament\Resources\GoalResource\RelationManagers\TasksRelationManager;
use App\Models\Goal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GoalResource extends Resource
{
    protected static ?string $model = Goal::class;
    protected static ?string $navigationIcon = 'heroicon-o-flag';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationGroup(): ?string { return __('Goals'); }
    public static function getNavigationLabel(): string { return __('Goals'); }
    public static function getModelLabel(): string { return __('Goal'); }
    public static function getPluralModelLabel(): string { return __('Goals'); }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::whereIn('status', ['in_progress', 'not_started'])->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Goal')->columns(2)->schema([
                Forms\Components\TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                Forms\Components\Select::make('category')
                    ->options([
                        'work' => 'Work', 'finance' => 'Finance', 'marriage' => 'Marriage',
                        'personal' => 'Personal', 'health' => 'Health', 'learning' => 'Learning',
                    ])
                    ->default('personal')->required()->native(false),
                Forms\Components\Select::make('horizon')
                    ->options([
                        'quarterly' => 'Quarterly',
                        'yearly' => 'Yearly',
                        'long_term' => 'Long-term',
                    ])
                    ->default('quarterly')->required()->native(false),
                Forms\Components\Select::make('status')
                    ->options([
                        'not_started' => 'Not Started',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'abandoned' => 'Abandoned',
                    ])
                    ->default('not_started')->required()->native(false),
                Forms\Components\Toggle::make('is_shared')->label('Share with partner'),
                Forms\Components\Textarea::make('description')->rows(3)->columnSpanFull(),
            ]),
            Forms\Components\Section::make('Timeline')->columns(3)->schema([
                Forms\Components\DatePicker::make('start_date')->default(now())->native(false),
                Forms\Components\DatePicker::make('target_date')->native(false),
                Forms\Components\TextInput::make('progress')->numeric()->minValue(0)->maxValue(100)->suffix('%')->default(0),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->wrap()->weight('bold'),
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'work' => 'info',
                        'finance' => 'success',
                        'marriage' => 'danger',
                        'personal' => 'gray',
                        'health' => 'warning',
                        'learning' => 'primary',
                    }),
                Tables\Columns\TextColumn::make('horizon')->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'not_started' => 'gray',
                        'in_progress' => 'info',
                        'completed' => 'success',
                        'abandoned' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('progress')
                    ->suffix('%')->sortable()->badge()
                    ->color(fn ($state) => $state >= 100 ? 'success' : ($state >= 50 ? 'info' : 'gray')),
                Tables\Columns\TextColumn::make('milestones_count')->counts('milestones')->label('Milestones')->badge(),
                Tables\Columns\TextColumn::make('target_date')->date()->sortable(),
                Tables\Columns\IconColumn::make('is_shared')->boolean()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')->options([
                    'work' => 'Work', 'finance' => 'Finance', 'marriage' => 'Marriage',
                    'personal' => 'Personal', 'health' => 'Health', 'learning' => 'Learning',
                ]),
                Tables\Filters\SelectFilter::make('horizon')->options([
                    'quarterly' => 'Quarterly', 'yearly' => 'Yearly', 'long_term' => 'Long-term',
                ]),
                Tables\Filters\SelectFilter::make('status')->options([
                    'not_started' => 'Not Started', 'in_progress' => 'In Progress',
                    'completed' => 'Completed', 'abandoned' => 'Abandoned',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->defaultSort('target_date');
    }

    public static function getRelations(): array
    {
        return [
            MilestonesRelationManager::class,
            TasksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGoals::route('/'),
            'create' => Pages\CreateGoal::route('/create'),
            'edit' => Pages\EditGoal::route('/{record}/edit'),
        ];
    }
}
