<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Filament\Resources\TaskResource\RelationManagers\SubtasksRelationManager;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;
    protected static ?string $navigationIcon = 'heroicon-o-check-circle';
    protected static ?int $navigationSort = 3;
    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationGroup(): ?string { return __('Work'); }
    public static function getNavigationLabel(): string { return __('Tasks'); }
    public static function getModelLabel(): string { return __('Task'); }
    public static function getPluralModelLabel(): string { return __('Tasks'); }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::query()
            ->whereNull('parent_task_id')
            ->whereIn('status', ['todo', 'in_progress'])
            ->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $overdue = static::getModel()::whereDate('due_date', '<', today())
            ->whereNotIn('status', ['done'])->count();
        if ($overdue > 0) return 'danger';
        $today = static::getModel()::whereDate('due_date', today())
            ->whereNotIn('status', ['done'])->count();
        return $today > 0 ? 'warning' : 'primary';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->columns(2)->schema([
                Forms\Components\TextInput::make('title')->required()->maxLength(255)->columnSpanFull()
                    ->label(__('Title')),
                Forms\Components\Select::make('status')
                    ->label(__('Status'))
                    ->options(self::statusOptions())
                    ->default('todo')->required()->native(false),
                Forms\Components\Select::make('priority')
                    ->label(__('Priority'))
                    ->options(self::priorityOptions())
                    ->default('medium')->required()->native(false),
                Forms\Components\Select::make('parent_task_id')
                    ->label(__('Parent task'))
                    ->relationship(
                        name: 'parent',
                        titleAttribute: 'title',
                        modifyQueryUsing: function (Builder $query) {
                            $current = request()->route('record');
                            if ($current) {
                                $query->where('id', '!=', $current);
                            }
                            $query->whereNull('parent_task_id');
                        },
                    )
                    ->searchable()->preload()
                    ->helperText(__('Make this a sub-task of an existing one')),
                Forms\Components\Select::make('sector_id')
                    ->label(__('Sector'))
                    ->relationship('sector', 'name')
                    ->searchable()->preload(),
                Forms\Components\Select::make('project_id')
                    ->label(__('Project'))
                    ->relationship('project', 'name')
                    ->searchable()->preload(),
                Forms\Components\Select::make('goal_id')
                    ->label(__('Goal'))
                    ->relationship('goal', 'title')
                    ->searchable()->preload()->live(),
                Forms\Components\Select::make('milestone_id')
                    ->label(__('Milestone'))
                    ->relationship(
                        name: 'milestone',
                        titleAttribute: 'title',
                        modifyQueryUsing: function (Builder $query) {
                            $goalId = request()->input('data.goal_id');
                            if ($goalId) {
                                $query->where('goal_id', $goalId);
                            }
                        },
                    )
                    ->searchable()->preload(),
                Forms\Components\Select::make('assigned_to')
                    ->label(__('Assigned to'))
                    ->relationship('assignee', 'name')
                    ->searchable()->preload(),
                Forms\Components\DatePicker::make('due_date')->label(__('Due date'))->native(false),
                Forms\Components\TextInput::make('estimated_minutes')->numeric()->suffix('min'),
                Forms\Components\TextInput::make('actual_minutes')->numeric()->suffix('min'),
                Forms\Components\Toggle::make('is_shared')->label(__('Share with partner')),
                Forms\Components\Textarea::make('description')->label(__('Description'))->rows(3)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->whereNull('parent_task_id');
            })
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->wrap()->weight('semibold')
                    ->label(__('Title'))
                    ->description(function (Task $r) {
                        $bits = [];
                        if ($r->milestone) $bits[] = '◆ ' . $r->milestone->title;
                        if ($r->goal) $bits[] = '⚐ ' . $r->goal->title;
                        return $bits ? implode(' · ', $bits) : null;
                    }),
                Tables\Columns\SelectColumn::make('status')
                    ->label(__('Status'))
                    ->options(self::statusOptions()),
                Tables\Columns\TextColumn::make('priority')
                    ->label(__('Priority'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'gray', 'medium' => 'info',
                        'high' => 'warning', 'urgent' => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => self::priorityOptions()[$state] ?? $state),
                Tables\Columns\TextColumn::make('subtasks_count')->counts('subtasks')
                    ->label(__('Sub'))
                    ->badge()->color('gray')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('sector.name')->label(__('Sector'))->badge()->toggleable(),
                Tables\Columns\TextColumn::make('project.name')->label(__('Project'))->toggleable()->limit(20),
                Tables\Columns\TextColumn::make('due_date')
                    ->label(__('Due date'))
                    ->date()->sortable()
                    ->color(fn ($state) => $state && $state->isPast() ? 'danger' : null),
                Tables\Columns\TextColumn::make('assignee.name')->label(__('Assigned to'))->toggleable(),
                Tables\Columns\IconColumn::make('is_shared')->boolean()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(self::statusOptions()),
                Tables\Filters\SelectFilter::make('priority')->options(self::priorityOptions()),
                Tables\Filters\SelectFilter::make('sector')->relationship('sector', 'name'),
                Tables\Filters\SelectFilter::make('project')->relationship('project', 'name'),
                Tables\Filters\SelectFilter::make('goal')->relationship('goal', 'title'),
                Tables\Filters\Filter::make('due_today')
                    ->label(__('Due today'))
                    ->query(fn ($q) => $q->whereDate('due_date', today())),
                Tables\Filters\Filter::make('overdue')
                    ->label(__('Overdue'))
                    ->query(fn ($q) => $q->whereDate('due_date', '<', today())->whereNotIn('status', ['done'])),
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

    public static function getRelations(): array
    {
        return [SubtasksRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }

    public static function statusOptions(): array
    {
        return [
            'todo' => __('To Do'),
            'in_progress' => __('In Progress'),
            'blocked' => __('Blocked'),
            'done' => __('Done'),
        ];
    }

    public static function priorityOptions(): array
    {
        return [
            'low' => __('Low'),
            'medium' => __('Medium'),
            'high' => __('High'),
            'urgent' => __('Urgent'),
        ];
    }
}
