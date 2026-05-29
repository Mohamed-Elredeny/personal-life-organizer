<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <span class="flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-banknotes" class="h-5 w-5 text-emerald-500" />
                {{ __('Balances by currency') }} · {{ $period_label }}
            </span>
        </x-slot>

        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
            @foreach ($rows as $row)
                @php
                    $netPositive = $row['net_raw'] >= 0;
                    $gradient = $netPositive
                        ? 'from-emerald-50 to-white dark:from-emerald-500/10 dark:to-transparent'
                        : 'from-rose-50 to-white dark:from-rose-500/10 dark:to-transparent';
                    $ring = $netPositive
                        ? 'ring-emerald-100 dark:ring-emerald-500/20'
                        : 'ring-rose-100 dark:ring-rose-500/20';
                @endphp
                <div class="relative overflow-hidden rounded-xl bg-gradient-to-br {{ $gradient }} p-4 ring-1 {{ $ring }} transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="text-[10px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                                {{ $row['code'] }}
                            </div>
                            <div class="mt-1 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                {{ $row['net'] }}
                            </div>
                        </div>
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white/70 text-lg font-bold text-gray-700 shadow-sm dark:bg-white/10 dark:text-gray-100">
                            {{ $row['symbol'] }}
                        </span>
                    </div>
                    <div class="mt-4 flex items-center gap-4 text-xs">
                        <div class="flex items-center gap-1.5">
                            <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                            <span class="text-gray-500 dark:text-gray-400">{{ __('Income') }}</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $row['in'] }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="inline-flex h-2 w-2 rounded-full bg-rose-500"></span>
                            <span class="text-gray-500 dark:text-gray-400">{{ __('Expense') }}</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $row['out'] }}</span>
                        </div>
                    </div>
                    @if (! $row['has_data'])
                        <div class="absolute inset-0 flex items-center justify-center bg-white/60 text-xs text-gray-400 backdrop-blur-[2px] dark:bg-gray-900/60">
                            {{ __('No data yet') }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
