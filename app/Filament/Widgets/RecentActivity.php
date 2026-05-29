<?php

namespace App\Filament\Widgets;

use App\Models\GratitudeLog;
use App\Models\Task;
use App\Models\Transaction;
use Filament\Widgets\Widget;

class RecentActivity extends Widget
{
    protected static string $view = 'filament.widgets.recent-activity';
    protected static ?int $sort = 7;
    protected static ?string $pollingInterval = '30s';
    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 6,
    ];

    public function getViewData(): array
    {
        $items = collect();

        Task::where('status', 'done')
            ->whereNotNull('completed_at')
            ->latest('completed_at')->limit(6)
            ->get()
            ->each(function ($t) use ($items) {
                $items->push([
                    'when' => $t->completed_at,
                    'type' => 'task',
                    'title' => $t->title,
                    'meta' => $t->sector?->name,
                    'icon' => 'heroicon-o-check-circle',
                    'color' => 'emerald',
                    'url' => \App\Filament\Resources\TaskResource::getUrl('edit', ['record' => $t]),
                ]);
            });

        Transaction::latest('occurred_on')->latest('id')->limit(6)
            ->get()
            ->each(function ($x) use ($items) {
                $items->push([
                    'when' => $x->occurred_on,
                    'type' => 'transaction',
                    'title' => ($x->type === 'income' ? '+ ' : '− ') . \App\Support\Money::format($x->amount, $x->currency, 0),
                    'meta' => trim(($x->category?->name ?? '—') . ' · ' . ($x->description ?? ''), ' ·'),
                    'icon' => $x->type === 'income' ? 'heroicon-o-arrow-up' : 'heroicon-o-arrow-down',
                    'color' => $x->type === 'income' ? 'emerald' : 'rose',
                    'url' => \App\Filament\Resources\TransactionResource::getUrl('edit', ['record' => $x]),
                ]);
            });

        GratitudeLog::latest('log_date')->limit(3)
            ->get()
            ->each(function ($g) use ($items) {
                $items->push([
                    'when' => $g->log_date,
                    'type' => 'gratitude',
                    'title' => \Illuminate\Support\Str::limit($g->content, 60),
                    'meta' => 'Gratitude · ' . ($g->user?->name ?? ''),
                    'icon' => 'heroicon-o-sparkles',
                    'color' => 'amber',
                    'url' => \App\Filament\Resources\GratitudeLogResource::getUrl('edit', ['record' => $g]),
                ]);
            });

        return [
            'items' => $items->sortByDesc('when')->take(10)->values(),
        ];
    }
}
