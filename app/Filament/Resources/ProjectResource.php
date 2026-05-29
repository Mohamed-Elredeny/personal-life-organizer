<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;
    protected static ?string $navigationIcon = 'heroicon-o-folder-open';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string { return __('Work'); }
    public static function getNavigationLabel(): string { return __('Projects'); }
    public static function getModelLabel(): string { return __('Project'); }
    public static function getPluralModelLabel(): string { return __('Projects'); }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::whereIn('status', ['planning', 'active'])->count();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Project Details')->columns(2)->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255)->columnSpanFull(),
                Forms\Components\Select::make('sector_id')
                    ->relationship('sector', 'name')
                    ->required()
                    ->preload()
                    ->searchable()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')->required(),
                        Forms\Components\ColorPicker::make('color')->default('#6366f1'),
                    ]),
                Forms\Components\Select::make('status')
                    ->options([
                        'planning' => 'Planning',
                        'active' => 'Active',
                        'on_hold' => 'On Hold',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('planning')
                    ->required()
                    ->native(false),
                Forms\Components\Select::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ])
                    ->default('medium')
                    ->required()
                    ->native(false),
                Forms\Components\Toggle::make('is_shared')->label('Share with partner'),
                Forms\Components\Textarea::make('description')->rows(3)->columnSpanFull(),
            ]),
            Forms\Components\Section::make('Timeline')->columns(3)->schema([
                Forms\Components\DatePicker::make('start_date')->native(false),
                Forms\Components\DatePicker::make('due_date')->native(false),
                Forms\Components\DatePicker::make('completed_at')->native(false),
                Forms\Components\TextInput::make('progress')
                    ->numeric()->minValue(0)->maxValue(100)->suffix('%')->default(0)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable()->weight('bold'),
                Tables\Columns\TextColumn::make('sector.name')
                    ->badge()
                    ->color(fn ($record) => $record->sector?->color ? null : 'gray'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'planning' => 'gray',
                        'active' => 'info',
                        'on_hold' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'gray',
                        'medium' => 'info',
                        'high' => 'warning',
                        'urgent' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('progress')
                    ->suffix('%')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state >= 100 ? 'success' : ($state >= 50 ? 'info' : 'gray')),
                Tables\Columns\TextColumn::make('due_date')->date()->sortable(),
                Tables\Columns\IconColumn::make('is_shared')->boolean()->label('Shared')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('sector')->relationship('sector', 'name'),
                Tables\Filters\SelectFilter::make('status')->options([
                    'planning' => 'Planning', 'active' => 'Active', 'on_hold' => 'On Hold',
                    'completed' => 'Completed', 'cancelled' => 'Cancelled',
                ]),
                Tables\Filters\SelectFilter::make('priority')->options([
                    'low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('due_date');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
