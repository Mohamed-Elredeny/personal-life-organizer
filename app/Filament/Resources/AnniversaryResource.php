<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnniversaryResource\Pages;
use App\Models\Anniversary;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AnniversaryResource extends Resource
{
    protected static ?string $model = Anniversary::class;
    protected static ?string $navigationIcon = 'heroicon-o-cake';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string { return __('Marriage'); }
    public static function getNavigationLabel(): string { return __('Anniversaries & Dates'); }
    public static function getModelLabel(): string { return __('Anniversary'); }
    public static function getPluralModelLabel(): string { return __('Anniversaries & Dates'); }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->columns(2)->schema([
                Forms\Components\TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                Forms\Components\Select::make('type')
                    ->options([
                        'anniversary' => 'Anniversary',
                        'birthday' => 'Birthday',
                        'date_night' => 'Date Night',
                        'milestone' => 'Milestone',
                        'other' => 'Other',
                    ])
                    ->default('anniversary')->required()->native(false),
                Forms\Components\DatePicker::make('date')->required()->native(false),
                Forms\Components\Toggle::make('is_recurring')->default(true)->label('Recurring annually'),
                Forms\Components\Toggle::make('is_shared')->default(true)->label('Share with partner'),
                Forms\Components\Textarea::make('description')->rows(3)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->weight('semibold'),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'anniversary' => 'danger',
                        'birthday' => 'warning',
                        'date_night' => 'info',
                        'milestone' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('date')->date()->sortable(),
                Tables\Columns\TextColumn::make('days_until')
                    ->label('Next in')
                    ->state(fn (Anniversary $r) => $r->daysUntilNext())
                    ->formatStateUsing(fn ($state) => $state === 0 ? 'Today' : $state . ' days')
                    ->color(fn ($state) => $state <= 7 ? 'danger' : ($state <= 30 ? 'warning' : 'gray')),
                Tables\Columns\IconColumn::make('is_recurring')->boolean(),
                Tables\Columns\IconColumn::make('is_shared')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->options([
                    'anniversary' => 'Anniversary',
                    'birthday' => 'Birthday',
                    'date_night' => 'Date Night',
                    'milestone' => 'Milestone',
                    'other' => 'Other',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->defaultSort('date');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnniversaries::route('/'),
            'create' => Pages\CreateAnniversary::route('/create'),
            'edit' => Pages\EditAnniversary::route('/{record}/edit'),
        ];
    }
}
