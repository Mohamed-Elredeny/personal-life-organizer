<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SectorResource\Pages;
use App\Models\Sector;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SectorResource extends Resource
{
    protected static ?string $model = Sector::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string { return __('Work'); }
    public static function getNavigationLabel(): string { return __('Sectors'); }
    public static function getModelLabel(): string { return __('Sector'); }
    public static function getPluralModelLabel(): string { return __('Sectors'); }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->columns(2)->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('AIU, Syncora, PhD outreach...'),
                Forms\Components\Select::make('icon')
                    ->options([
                        'heroicon-o-academic-cap' => 'Academic',
                        'heroicon-o-building-office' => 'Office',
                        'heroicon-o-code-bracket' => 'Code',
                        'heroicon-o-rocket-launch' => 'Startup',
                        'heroicon-o-beaker' => 'Research',
                        'heroicon-o-heart' => 'Personal',
                        'heroicon-o-currency-dollar' => 'Finance',
                    ])
                    ->searchable(),
                Forms\Components\ColorPicker::make('color')->default('#6366f1'),
                Forms\Components\Toggle::make('is_shared')
                    ->label('Share with partner')
                    ->helperText('Visible to your partner'),
                Forms\Components\Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_archived')
                    ->label('Archived')
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ColorColumn::make('color'),
                Tables\Columns\IconColumn::make('icon')->icon(fn ($state) => $state)->default('heroicon-o-rectangle-stack'),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable()->weight('bold'),
                Tables\Columns\TextColumn::make('projects_count')->counts('projects')->label('Projects')->badge(),
                Tables\Columns\TextColumn::make('tasks_count')->counts('tasks')->label('Tasks')->badge(),
                Tables\Columns\IconColumn::make('is_shared')->boolean()->label('Shared'),
                Tables\Columns\IconColumn::make('is_archived')->boolean()->label('Archived')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_archived')->default(false),
                Tables\Filters\TernaryFilter::make('is_shared'),
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
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSectors::route('/'),
            'create' => Pages\CreateSector::route('/create'),
            'edit' => Pages\EditSector::route('/{record}/edit'),
        ];
    }
}
