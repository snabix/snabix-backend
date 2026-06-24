<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-5">
            <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br {{ $summary['tone']['panel'] }} p-6 text-white shadow-2xl ring-1 {{ $summary['tone']['ring'] }} sm:p-7">
                <div class="pointer-events-none absolute -right-20 -top-24 size-72 rounded-full {{ $summary['tone']['glow'] }} blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-28 left-1/4 size-80 rounded-full bg-sky-300/10 blur-3xl"></div>
                <div class="pointer-events-none absolute inset-0 opacity-[0.18] [background-image:linear-gradient(135deg,rgba(255,255,255,.22)_1px,transparent_1px)] [background-size:28px_28px]"></div>

                <div class="relative grid gap-6 xl:grid-cols-[minmax(0,1.08fr)_minmax(320px,.92fr)] xl:items-end">
                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-[11px] font-black uppercase tracking-[0.22em] ring-1 {{ $summary['tone']['badge'] }}">
                                System Pulse
                            </span>
                            <span class="text-xs font-semibold uppercase tracking-[0.18em] text-white/50">
                                Обновлено {{ $summary['updatedAt'] }}
                            </span>
                        </div>

                        <h2 class="mt-5 max-w-3xl text-3xl font-black tracking-[-0.04em] sm:text-4xl">
                            {{ $summary['label'] }}
                        </h2>

                        <p class="mt-3 max-w-2xl text-sm leading-6 text-white/70">
                            {{ $summary['caption'] }}
                        </p>
                    </div>

                    <div class="rounded-[1.75rem] border border-white/12 bg-white/[0.08] p-5 shadow-xl backdrop-blur-xl">
                        <div class="flex items-end justify-between gap-5">
                            <div>
                                <p class="text-[11px] font-black uppercase tracking-[0.2em] text-white/50">
                                    Индекс здоровья
                                </p>
                                <div class="mt-2 text-6xl font-black leading-none tracking-[-0.08em]">
                                    {{ $summary['healthPercent'] }}%
                                </div>
                            </div>

                            <div class="grid size-20 place-items-center rounded-full border border-white/14 bg-white/10 text-sm font-black text-white/80">
                                {{ $summary['ok'] }}/{{ $summary['total'] }}
                            </div>
                        </div>

                        <div class="mt-5 h-2 overflow-hidden rounded-full bg-white/12">
                            <div class="h-full rounded-full bg-white shadow-[0_0_26px_rgba(255,255,255,.42)]" style="width: {{ $summary['healthPercent'] }}%"></div>
                        </div>

                        <div class="mt-5 grid grid-cols-3 gap-2 text-center">
                            <div class="rounded-2xl bg-emerald-300/12 px-3 py-3">
                                <p class="text-2xl font-black">{{ $summary['ok'] }}</p>
                                <p class="mt-1 text-[10px] font-bold uppercase tracking-[0.14em] text-emerald-100/70">OK</p>
                            </div>
                            <div class="rounded-2xl bg-amber-300/12 px-3 py-3">
                                <p class="text-2xl font-black">{{ $summary['degraded'] }}</p>
                                <p class="mt-1 text-[10px] font-bold uppercase tracking-[0.14em] text-amber-100/70">Внимание</p>
                            </div>
                            <div class="rounded-2xl bg-rose-300/12 px-3 py-3">
                                <p class="text-2xl font-black">{{ $summary['problem'] }}</p>
                                <p class="mt-1 text-[10px] font-bold uppercase tracking-[0.14em] text-rose-100/70">Ошибки</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 xl:grid-cols-2">
                @foreach ($checks as $check)
                    <article class="group overflow-hidden rounded-[1.75rem] border border-gray-200/80 bg-white shadow-sm ring-1 ring-black/5 transition-colors hover:border-primary-300/70 dark:border-white/10 dark:bg-white/[0.035] dark:ring-white/10 dark:hover:border-primary-400/40">
                        <div class="bg-gradient-to-br {{ $check['status_tone'] }} p-5 ring-1">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-3">
                                        <span class="grid size-9 shrink-0 place-items-center rounded-full bg-white/70 text-sm font-black text-gray-950 ring-1 ring-black/5 dark:bg-white/10 dark:text-white dark:ring-white/10">
                                            {{ $check['status_icon'] }}
                                        </span>
                                        <div>
                                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">
                                                {{ $check['status_label'] }}
                                            </p>
                                            <h3 class="mt-1 text-lg font-black tracking-[-0.02em] text-gray-950 dark:text-white">
                                                {{ $check['label'] }}
                                            </h3>
                                        </div>
                                    </div>

                                    @if (filled($check['message']))
                                        <p class="mt-4 max-w-xl text-sm leading-6 text-gray-600 dark:text-gray-300">
                                            {{ $check['message'] }}
                                        </p>
                                    @endif
                                </div>

                                <x-filament::badge :color="$check['badge_color']">
                                    {{ $check['status_label'] }}
                                </x-filament::badge>
                            </div>
                        </div>

                        <div class="space-y-4 p-5">
                            @if (! empty($check['highlights']))
                                <div class="grid gap-3 sm:grid-cols-2">
                                    @foreach ($check['highlights'] as $highlight)
                                        <div class="rounded-2xl border border-gray-100 bg-gray-50/80 p-4 dark:border-white/10 dark:bg-white/[0.04]">
                                            <div class="flex items-center justify-between gap-3">
                                                <span class="text-sm font-semibold text-gray-600 dark:text-gray-300">{{ $highlight['label'] }}</span>
                                                <span class="text-sm font-black text-gray-950 dark:text-white">{{ $highlight['value'] }}</span>
                                            </div>
                                            <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-white/10">
                                                <div class="h-full rounded-full {{ $highlight['color'] }}" style="width: {{ $highlight['percent'] }}%"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if (! empty($check['context_items']))
                                <dl class="grid gap-3 sm:grid-cols-2">
                                    @foreach ($check['context_items'] as $item)
                                        <div class="rounded-2xl border border-gray-100 bg-white px-4 py-3 shadow-sm dark:border-white/10 dark:bg-white/[0.035]">
                                            <dt class="text-[11px] font-black uppercase tracking-[0.13em] text-gray-500 dark:text-gray-400">
                                                {{ $item['label'] }}
                                            </dt>
                                            <dd class="mt-2 break-all text-sm font-semibold text-gray-950 dark:text-white">
                                                {{ $item['value'] }}
                                            </dd>
                                        </div>
                                    @endforeach
                                </dl>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
