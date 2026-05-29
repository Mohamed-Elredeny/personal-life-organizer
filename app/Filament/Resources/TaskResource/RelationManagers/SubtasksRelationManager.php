<?php

namespace App\Filament\Resources\TaskResource\RelationManagers;

use App\Filament\Resources\TaskResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SubtasksRelationManager extends RelationManager
{
    protected static string $relationship = 'subtasks';
    protected static ?string $recordTitleAttribute = 'title';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('Sub-tasks');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->required()->maxLength(255)->columnSpanFull()
                ->label(__('Title')),
            Forms\Components\Select::make('status')
                ->label(__('Status'))
                ->options(TaskResource::statusOptions())
                ->default('todo')->required()->native(false),
            Forms\Components\Select::make('priority')
                ->label(__('Priority'))
                ->options(TaskResource::priorityOptions())
                ->default('medium')->required()->native(false),
            Forms\Components\Select::make('assigned_to')
                ->label(__('Assigned to'))
                ->relationship('assignee', 'name')
                ->searchable()->preload(),
            Forms\Components\DatePicker::make('due_date')->label(__('Due date'))->native(false),
            Forms\Components\Textarea::make('description')->label(__('Description'))->rows(2)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->weight('semibold')->wrap()->searchable()
                    ->label(__('Title')),
                Tables\Columns\SelectColumn::make('status')
                    ->label(__('Status'))
                    ->options(TaskResource::statusOptions()),
                Tables\Columns\TextColumn::make('priority')
                    ->label(__('Priority'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'gray', 'medium' => 'info',
                        'high' => 'warning', 'urgent' => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => TaskResource::priorityOptions()[$state] ?? $state),
                Tables\Columns\TextColumn::make('assignee.name')->label(__('Assigned to'))->placeholder('—'),
                Tables\Columns\TextColumn::make('due_date')->label(__('Due date'))->date()
                    ->color(fn ($state) => $state && $state->isPast() ? 'danger' : null),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()->icon('heroicon-m-plus')])
            ->actions([
                Tables\Actions\Action::make('complete')
                    ->iconButton()
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->action(fn ($record) => $record->update(['status' => 'done']))
                    ->visible(fn ($record) => $record->status !== 'done')
                    ->tooltip(__('Mark done')),
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }
}
