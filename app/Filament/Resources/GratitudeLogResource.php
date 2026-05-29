<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GratitudeLogResource\Pages;
use App\Models\GratitudeLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GratitudeLogResource extends Resource
{
    protected static ?string $model = GratitudeLog::class;
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string { return __('Marriage'); }
    public static function getNavigationLabel(): string { return __('Gratitude Journal'); }
    public static function getModelLabel(): string { return __('Gratitude'); }
    public static function getPluralModelLabel(): string { return __('Gratitude Journal'); }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->columns(2)->schema([
                Forms\Components\DatePicker::make('log_date')->default(now())->required()->native(false),
                Forms\Components\Select::make('mood')
                    ->options([
                        1 => '1 — Tough day',
                        2 => '2 — Meh',
                        3 => '3 — Okay',
                        4 => '4 — Good',
                        5 => '5 — Amazing',
                    ])
                    ->native(false),
                Forms\Components\Textarea::make('content')
                    ->required()
                    ->rows(5)
                    ->placeholder('What are you grateful for today?')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_shared')->default(true)->label('Share with partner'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('log_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('By')->badge(),
                Tables\Columns\TextColumn::make('content')->wrap()->limit(120)->searchable(),
                Tables\Columns\TextColumn::make('mood')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? str_repeat('★', $state) : '—')
                    ->color(fn ($state) => match ($state) {
                        1, 2 => 'danger',
                        3 => 'warning',
                        4, 5 => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_shared')->boolean(),
            ])
            ->filters([
                Tables\Filters\Filter::make('this_week')
                    ->label('This week')
                    ->query(fn ($q) => $q->whereBetween('log_date', [now()->startOfWeek(), now()->endOfWeek()])),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->defaultSort('log_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGratitudeLogs::route('/'),
            'create' => Pages\CreateGratitudeLog::route('/create'),
            'edit' => Pages\EditGratitudeLog::route('/{record}/edit'),
        ];
    }
}
