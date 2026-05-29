<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <span class="flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-squares-2x2" class="h-5 w-5 text-indigo-500" />
                {{ __('Sector workload') }}
            </span>
        </x-slot>

        @if ($sectors->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No sectors yet.') }}</p>
        @else
            <ul role="list" class="space-y-4">
                @foreach ($sectors as $sector)
                    @php
                        $total = $sector->open_tasks + $sector->done_tasks;
                        $donePct = $total > 0 ? (int) round(($sector->done_tasks / $total) * 100) : 0;
                        $barPct = (int) round(($sector->open_tasks / $maxOpen) * 100);
                    @endphp
                    <li>
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="h-2.5 w-2.5 flex-shrink-0 rounded-full" style="background:{{ $sector->color }}"></span>
                                <a href="{{ \App\Filament\Resources\SectorResource::getUrl('edit', ['record' => $sector]) }}"
                                   class="truncate text-sm font-semibold text-gray-900 hover:text-indigo-600 dark:text-white dark:hover:text-indigo-400">
                                    {{ $sector->name }}
                                </a>
                            </div>
                            <div class="flex flex-shrink-0 items-center gap-3 text-xs">
                                <span class="text-gray-500 dark:text-gray-400">
                                    {{ $sector->active_projects }} {{ __('proj') }}
                                </span>
                                <span class="rounded-md bg-indigo-50 px-2 py-0.5 font-semibold text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300">
                                    {{ $sector->open_tasks }} {{ __('open') }}
                                </span>
                                <span class="text-emerald-600 dark:text-emerald-400">
                                    {{ $donePct }}%
                                </span>
                            </div>
                        </div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-white/5">
                            <div class="h-full rounded-full transition-all duration-500"
                                 style="width: {{ $barPct }}%; background:{{ $sector->color }}"></div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
