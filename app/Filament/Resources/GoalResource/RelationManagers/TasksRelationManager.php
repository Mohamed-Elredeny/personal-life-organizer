<?php

namespace App\Filament\Resources\GoalResource\RelationManagers;

use App\Filament\Resources\TaskResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';
    protected static ?string $recordTitleAttribute = 'title';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('Tasks');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->required()->maxLength(255)->columnSpanFull()
                ->label(__('Title')),
            Forms\Components\Select::make('milestone_id')
                ->label(__('Milestone'))
                ->relationship(
                    name: 'milestone',
                    titleAttribute: 'title',
                    modifyQueryUsing: fn (Builder $q) => $q->where('goal_id', $this->ownerRecord->id),
                )
                ->searchable()->preload(),
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
            ->modifyQueryUsing(fn (Builder $q) => $q->whereNull('parent_task_id'))
            ->columns([
                Tables\Columns\TextColumn::make('title')->weight('semibold')->wrap()->searchable()
                    ->description(fn ($r) => $r->milestone ? '◆ ' . $r->milestone->title : null),
                Tables\Columns\SelectColumn::make('status')
                    ->options(TaskResource::statusOptions()),
                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'gray', 'medium' => 'info',
                        'high' => 'warning', 'urgent' => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => TaskResource::priorityOptions()[$state] ?? $state),
                Tables\Columns\TextColumn::make('subtasks_count')->counts('subtasks')
                    ->label(__('Sub'))->badge()->color('gray')->placeholder('—'),
                Tables\Columns\TextColumn::make('due_date')->label(__('Due date'))->date()
                    ->color(fn ($state) => $state && $state->isPast() ? 'danger' : null),
                Tables\Columns\TextColumn::make('assignee.name')->label(__('Assigned to')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('milestone_id')
                    ->label(__('Milestone'))
                    ->relationship(
                        name: 'milestone',
                        titleAttribute: 'title',
                        modifyQueryUsing: fn (Builder $q) => $q->where('goal_id', $this->ownerRecord->id),
                    ),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()->icon('heroicon-m-plus')])
            ->actions([
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->defaultGroup('milestone.title');
    }
}
