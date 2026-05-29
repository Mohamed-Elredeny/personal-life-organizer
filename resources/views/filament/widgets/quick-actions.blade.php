<x-filament-widgets::widget>
    <div class="flex flex-col gap-4 rounded-2xl border border-gray-200 bg-gradient-to-br from-white to-gray-50 p-5 shadow-sm dark:border-white/10 dark:from-gray-900 dark:to-gray-900/50 md:flex-row md:items-center md:justify-between">
        {{-- Period segmented control --}}
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Period') }}</span>
            <div class="inline-flex rounded-xl bg-gray-100 p-1 dark:bg-white/5" role="tablist">
                @foreach ($periods as $key => $label)
                    <button
                        type="button"
                        wire:click="setPeriod('{{ $key }}')"
                        @class([
                            'rounded-lg px-3 py-1.5 text-sm font-medium transition',
                            'bg-white text-indigo-600 shadow-sm dark:bg-gray-800 dark:text-indigo-400' => $period === $key,
                            'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100' => $period !== $key,
                        ])
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Quick add buttons --}}
        <div class="flex flex-wrap gap-2">
            <a href="{{ \App\Filament\Resources\TaskResource::getUrl('create') }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-700 dark:border-white/10 dark:bg-white/5 dark:text-gray-200 dark:hover:bg-indigo-500/10">
                <x-filament::icon icon="heroicon-m-plus" class="h-4 w-4" /> {{ __('Task') }}
            </a>
            <a href="{{ \App\Filament\Resources\TransactionResource::getUrl('create') }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-700 dark:border-white/10 dark:bg-white/5 dark:text-gray-200 dark:hover:bg-emerald-500/10">
                <x-filament::icon icon="heroicon-m-banknotes" class="h-4 w-4" /> {{ __('Transaction') }}
            </a>
            <a href="{{ \App\Filament\Resources\GoalResource::getUrl('create') }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:border-sky-300 hover:bg-sky-50 hover:text-sky-700 dark:border-white/10 dark:bg-white/5 dark:text-gray-200 dark:hover:bg-sky-500/10">
                <x-filament::icon icon="heroicon-m-flag" class="h-4 w-4" /> {{ __('Goal') }}
            </a>
            <a href="{{ \App\Filament\Resources\GratitudeLogResource::getUrl('create') }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:border-rose-300 hover:bg-rose-50 hover:text-rose-700 dark:border-white/10 dark:bg-white/5 dark:text-gray-200 dark:hover:bg-rose-500/10">
                <x-filament::icon icon="heroicon-m-sparkles" class="h-4 w-4" /> {{ __('Gratitude') }}
            </a>
        </div>
    </div>
</x-filament-widgets::widget>
