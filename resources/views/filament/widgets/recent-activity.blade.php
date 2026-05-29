<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <span class="flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-clock" class="h-5 w-5 text-indigo-500" />
                {{ __('Recent activity') }}
            </span>
        </x-slot>

        @if ($items->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Nothing yet — get started with a task or transaction.') }}</p>
        @else
            <ol class="relative space-y-4 border-s border-gray-200 ps-5 dark:border-white/10">
                @foreach ($items as $item)
                    <li class="relative">
                        <span class="absolute -start-[26px] flex h-5 w-5 items-center justify-center rounded-full bg-{{ $item['color'] }}-100 ring-4 ring-white dark:bg-{{ $item['color'] }}-500/20 dark:ring-gray-900">
                            <x-filament::icon :icon="$item['icon']" class="h-3 w-3 text-{{ $item['color'] }}-600 dark:text-{{ $item['color'] }}-400" />
                        </span>
                        <a href="{{ $item['url'] }}" class="block group">
                            <p class="text-sm font-medium text-gray-900 group-hover:text-indigo-600 dark:text-white dark:group-hover:text-indigo-400">
                                {{ $item['title'] }}
                            </p>
                            <p class="mt-0.5 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                <span>{{ $item['meta'] }}</span>
                                <span>·</span>
                                <span>{{ $item['when']->diffForHumans() }}</span>
                            </p>
                        </a>
                    </li>
                @endforeach
            </ol>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
