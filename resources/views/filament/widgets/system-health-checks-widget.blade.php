<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ 'Состояние системы' }}
        </x-slot>

        <div class="mb-5 overflow-hidden rounded-3xl bg-gradient-to-r from-slate-950 via-slate-900 to-sky-900 p-6 text-white shadow-xl ring-1 ring-white/10">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div class="max-w-2xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-sky-200/80">System Pulse</p>
                    <h3 class="mt-2 text-2xl font-semibold">Наглядный срез по ключевым проверкам инфраструктуры</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-200/80">
                        Виджет собирает состояние сервисов, базы, кэша и системных ресурсов в одном месте,
                        чтобы проблемы были заметны ещё до того, как они станут инцидентом.
                    </p>
                </div>

                <div class="grid min-w-[280px] flex-1 gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl bg-white/10 px-4 py-4 backdrop-blur">
                        <div class="text-xs uppercase tracking-[0.2em] text-slate-300">Всего проверок</div>
                        <div class="mt-2 text-3xl font-semibold">{{ $summary['total'] }}</div>
                    </div>
                    <div class="rounded-2xl bg-emerald-400/15 px-4 py-4 backdrop-blur">
                        <div class="text-xs uppercase tracking-[0.2em] text-emerald-100/80">Стабильно</div>
                        <div class="mt-2 text-3xl font-semibold">{{ $summary['ok'] }}</div>
                    </div>
                    <div class="rounded-2xl bg-amber-300/15 px-4 py-4 backdrop-blur">
                        <div class="text-xs uppercase tracking-[0.2em] text-amber-100/80">Требует внимания</div>
                        <div class="mt-2 text-3xl font-semibold">{{ $summary['degraded'] + $summary['problem'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-4 xl:grid-cols-2">
            @foreach ($checks as $check)
                <div class="overflow-hidden rounded-3xl border border-gray-200/80 bg-white shadow-sm ring-1 ring-black/5 dark:border-white/10 dark:bg-white/5 dark:ring-white/10">
                    <div class="bg-gradient-to-r {{ $check['status_tone'] }} p-5 ring-1">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.22em] text-gray-500 dark:text-gray-300">
                                    <span>{{ $check['status_icon'] }}</span>
                                    <span>{{ $check['status_label'] }}</span>
                                </div>
                                <h3 class="mt-3 text-lg font-semibold text-gray-950 dark:text-white">
                                    {{ $check['label'] }}
                                </h3>

                                @if (filled($check['message']))
                                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">
                                        {{ $check['message'] }}
                                    </p>
                                @endif
                            </div>

                            <x-filament::badge :color="$check['badge_color']">
                                {{ $check['status_label'] }}
                            </x-filament::badge>
                        </div>
                    </div>

                    <div class="p-5">
                        @if (! empty($check['highlights']))
                            <div class="grid gap-3 md:grid-cols-2">
                                @foreach ($check['highlights'] as $highlight)
                                    <div class="rounded-2xl border border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-white/10 dark:bg-white/5">
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ $highlight['label'] }}</span>
                                            <span class="text-sm font-semibold text-gray-950 dark:text-white">{{ $highlight['value'] }}</span>
                                        </div>
                                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-white/10">
                                            <div class="h-full rounded-full {{ $highlight['color'] }}" style="width: {{ $highlight['percent'] }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if (! empty($check['context_items']))
                            <dl class="mt-5 grid gap-3 md:grid-cols-2">
                                @foreach ($check['context_items'] as $item)
                                    <div class="rounded-2xl border border-gray-100 bg-white px-4 py-3 shadow-sm dark:border-white/10 dark:bg-white/5">
                                        <dt class="text-[11px] font-semibold uppercase tracking-[0.12em] text-gray-500 dark:text-gray-400">
                                            {{ $item['label'] }}
                                        </dt>
                                        <dd class="mt-2 break-all text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $item['value'] }}
                                        </dd>
                                    </div>
                                @endforeach
                            </dl>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
