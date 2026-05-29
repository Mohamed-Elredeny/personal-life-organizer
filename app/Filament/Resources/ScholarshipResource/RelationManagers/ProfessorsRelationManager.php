<?php

namespace App\Filament\Resources\ScholarshipResource\RelationManagers;

use App\Filament\Resources\ProfessorResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ProfessorsRelationManager extends RelationManager
{
    protected static string $relationship = 'professors';
    protected static ?string $recordTitleAttribute = 'name';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('Professors');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(255)->columnSpanFull(),
            Forms\Components\TextInput::make('university')->maxLength(255),
            Forms\Components\TextInput::make('email')->email()->maxLength(255),
            Forms\Components\Select::make('status')
                ->options(ProfessorResource::statusOptions())
                ->default('planned')->required()->native(false),
            Forms\Components\DatePicker::make('next_follow_up_at')->native(false),
            Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->weight('semibold')->searchable()
                    ->description(fn ($r) => $r->university),
                Tables\Columns\TextColumn::make('email')->copyable()->limit(25)->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => ProfessorResource::statusColor($state))
                    ->formatStateUsing(fn ($state) => ProfessorResource::statusOptions()[$state] ?? $state),
                Tables\Columns\TextColumn::make('next_follow_up_at')->label(__('Follow-up'))->date(),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }
}
