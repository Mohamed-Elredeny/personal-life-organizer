<?php

namespace App\Filament\Widgets;

use App\Models\Task;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingTasksTable extends BaseWidget
{
    public function getTableHeading(): ?string { return __('Upcoming tasks'); }
    protected static ?int $sort = 6;
    protected static ?string $pollingInterval = '30s';
    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 6,
    ];

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Task::query()
                    ->whereNotIn('status', ['done'])
                    ->where(function ($q) {
                        $q->whereNull('due_date')
                            ->orWhere('due_date', '<=', now()->addDays(14));
                    })
                    ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END, due_date ASC')
                    ->limit(8)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->wrap()
                    ->weight('semibold')
                    ->description(fn (Task $r) => $r->sector?->name ?? $r->project?->name),
                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'low' => 'gray', 'medium' => 'info',
                        'high' => 'warning', 'urgent' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Due')
                    ->formatStateUsing(function ($state) {
                        if (! $state) return '—';
                        $diff = (int) now()->startOfDay()->diffInDays($state, false);
                        if ($diff < 0) return abs($diff) . 'd overdue';
                        if ($diff === 0) return 'Today';
                        if ($diff === 1) return 'Tomorrow';
                        return 'in ' . $diff . 'd';
                    })
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        ! $state => 'gray',
                        $state->isPast() => 'danger',
                        $state->isToday() => 'warning',
                        $state->lessThanOrEqualTo(now()->addDays(3)) => 'info',
                        default => 'gray',
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('complete')
                    ->iconButton()
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->action(fn (Task $r) => $r->update(['status' => 'done']))
                    ->visible(fn (Task $r) => $r->status !== 'done')
                    ->tooltip('Mark done'),
                Tables\Actions\EditAction::make()->iconButton(),
            ])
            ->paginated(false);
    }
}
