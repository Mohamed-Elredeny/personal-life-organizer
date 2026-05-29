<?php

namespace App\Filament\Resources\GoalResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MilestonesRelationManager extends RelationManager
{
    protected static string $relationship = 'milestones';
    protected static ?string $recordTitleAttribute = 'title';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
            Forms\Components\Textarea::make('description')->rows(2)->columnSpanFull(),
            Forms\Components\DatePicker::make('due_date')->native(false),
            Forms\Components\TextInput::make('sort')->numeric()->default(0),
            Forms\Components\Toggle::make('is_completed'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->columns([
                Tables\Columns\TextColumn::make('sort')->label('#')->sortable(),
                Tables\Columns\CheckboxColumn::make('is_completed')->label(__('Done')),
                Tables\Columns\TextColumn::make('title')->wrap()->weight('semibold')->label(__('Title')),
                Tables\Columns\TextColumn::make('tasks_count')->counts('tasks')->label(__('Tasks'))->badge(),
                Tables\Columns\TextColumn::make('due_date')->date()->sortable()->label(__('Due date')),
                Tables\Columns\TextColumn::make('completed_at')->date()->placeholder('—')->label(__('Completed at')),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->defaultSort('sort');
    }
}
