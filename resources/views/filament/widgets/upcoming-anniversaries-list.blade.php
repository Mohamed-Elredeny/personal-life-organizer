<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <span class="flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-heart" class="h-5 w-5 text-rose-500" />
                {{ __('Upcoming dates') }}
            </span>
        </x-slot>

        @if ($items->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No anniversaries or dates yet. Add some in the Marriage module.') }}</p>
        @else
            <ul role="list" class="divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($items as $item)
                    @php
                        $days = $item->daysUntilNext();
                        $color = $days <= 7 ? 'rose' : ($days <= 30 ? 'amber' : 'slate');
                    @endphp
                    <li class="flex items-center justify-between py-3">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-{{ $color }}-100 dark:bg-{{ $color }}-500/15 text-{{ $color }}-600 dark:text-{{ $color }}-400">
                                <x-filament::icon icon="heroicon-o-cake" class="h-5 w-5" />
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $item->title }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $item->nextOccurrence()->format('D, M j, Y') }}
                                    @if ($item->is_recurring)
                                        · recurring
                                    @endif
                                </p>
                            </div>
                        </div>
                        <span @class([
                            'rounded-full px-3 py-1 text-xs font-medium',
                            'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300' => $days <= 7,
                            'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300' => $days > 7 && $days <= 30,
                            'bg-slate-100 text-slate-700 dark:bg-slate-500/15 dark:text-slate-300' => $days > 30,
                        ])>
                            {{ $days === 0 ? 'Today' : "in {$days} days" }}
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
