<?php

declare(strict_types=1);

namespace App\Shared\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use UKFast\HealthCheck\Facade\HealthCheck;
use UKFast\HealthCheck\HealthCheck as HealthCheckContract;

class SystemHealthChecksWidget extends Widget
{
    protected string $view                     = 'filament.widgets.system-health-checks-widget';

    protected int | string | array $columnSpan = 'full';

    public function getHeading(): string
    {
        return 'Состояние системы';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $checks = $this->resolveStatuses()->map(function (array $check): array {
            $status = $check['status'] ?? 'UNKNOWN';

            return [
                ...$check,
                'label'         => $this->resolveCheckLabel($check['name']),
                'status_label'  => $this->resolveStatusLabel($status),
                'badge_color'   => $this->resolveBadgeColor($status),
                'status_tone'   => $this->resolveStatusTone($status),
                'status_icon'   => $this->resolveStatusIcon($status),
                'context_items' => $this->resolveContextItems($check['context']),
                'highlights'    => $this->resolveHighlights($check['context']),
            ];
        });

        return [
            'checks'  => $checks,
            'summary' => [
                'total'    => $checks->count(),
                'ok'       => $checks->where('status', 'OK')->count(),
                'degraded' => $checks->where('status', 'DEGRADED')->count(),
                'problem'  => $checks->where('status', 'PROBLEM')->count(),
            ],
        ];
    }

    /**
     * @return Collection<int, array{name: string, status: string|null, message: string, context: array<string, array<string, string>|int|string>}>
     */
    private function resolveStatuses(): Collection
    {
        /** @var Collection<int, HealthCheckContract> $checks */
        $checks = HealthCheck::all();

        return $checks
            ->map(
                function (HealthCheckContract $check): array {
                    $status = $check->status();

                    return [
                        'name'    => $check->name(),
                        'status'  => $status->getStatus(),
                        'message' => $status->message(),
                        'context' => $status->context(),
                    ];
                },
            );
    }

    private function resolveCheckLabel(string $name): string
    {
        return match ($name) {
            'database'         => 'База данных',
            'redis'            => 'Redis',
            'cache'            => 'Кэш',
            'storage'          => 'Файловое хранилище',
            'migration'        => 'Миграции',
            'env'              => 'Окружение',
            'system_resources' => 'Ресурсы системы',
            default            => str($name)->replace('_', ' ')->headline()->toString(),
        };
    }

    private function resolveStatusLabel(?string $status): string
    {
        return match ($status) {
            'OK'       => 'Работает',
            'DEGRADED' => 'Требует внимания',
            'PROBLEM'  => 'Ошибка',
            default    => 'Неизвестно',
        };
    }

    private function resolveBadgeColor(?string $status): string
    {
        return match ($status) {
            'OK'       => 'success',
            'DEGRADED' => 'warning',
            'PROBLEM'  => 'danger',
            default    => 'gray',
        };
    }

    private function resolveStatusTone(?string $status): string
    {
        return match ($status) {
            'OK'       => 'from-emerald-500/15 to-emerald-500/5 ring-emerald-500/20',
            'DEGRADED' => 'from-amber-500/15 to-amber-500/5 ring-amber-500/20',
            'PROBLEM'  => 'from-rose-500/15 to-rose-500/5 ring-rose-500/20',
            default    => 'from-slate-500/10 to-slate-500/5 ring-slate-500/10',
        };
    }

    private function resolveStatusIcon(?string $status): string
    {
        return match ($status) {
            'OK'       => '●',
            'DEGRADED' => '▲',
            'PROBLEM'  => '■',
            default    => '○',
        };
    }

    /**
     * @param  array<string, mixed>                      $context
     * @return list<array{label: string, value: string}>
     */
    private function resolveContextItems(array $context): array
    {
        $items = [];

        foreach ($context as $key => $value) {
            $items[] = [
                'label' => $this->resolveContextLabel($key),
                'value' => $this->stringifyContextValue($value),
            ];
        }

        return $items;
    }

    private function resolveContextLabel(string $key): string
    {
        return match ($key) {
            'media_disk'               => 'Медиа-диск',
            'media_root'               => 'Корневая директория',
            'disk_free_gb'             => 'Свободно, ГБ',
            'disk_total_gb'            => 'Всего, ГБ',
            'disk_used_gb'             => 'Использовано, ГБ',
            'disk_usage_percent'       => 'Заполнение диска, %',
            'php_memory_usage_mb'      => 'Использование PHP, МБ',
            'php_peak_memory_mb'       => 'Пиковая память PHP, МБ',
            'php_memory_limit_mb'      => 'Лимит памяти PHP, МБ',
            'php_memory_usage_percent' => 'Использование памяти, %',
            default                    => str($key)->replace('_', ' ')->headline()->toString(),
        };
    }

    /**
     * @param  array<string, mixed>                                                   $context
     * @return list<array{label: string, value: string, percent: int, color: string}>
     */
    private function resolveHighlights(array $context): array
    {
        $highlights = [];

        foreach ([
            'disk_usage_percent'       => 'Заполнение диска',
            'php_memory_usage_percent' => 'Память PHP',
        ] as $key => $label) {
            $value        = $context[$key] ?? null;

            if (! is_int($value) && ! is_numeric($value)) {
                continue;
            }

            $percent      = max(0, min(100, (int) $value));

            $highlights[] = [
                'label'   => $label,
                'value'   => $percent . '%',
                'percent' => $percent,
                'color'   => $this->resolvePercentColor($percent),
            ];
        }

        return $highlights;
    }

    private function resolvePercentColor(int $percent): string
    {
        return match (true) {
            $percent >= 90 => 'bg-rose-500',
            $percent >= 75 => 'bg-amber-500',
            default        => 'bg-emerald-500',
        };
    }

    private function stringifyContextValue(mixed $value): string
    {
        if (is_array($value)) {
            $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            return is_string($json) ? $json : '-';
        }

        if (is_bool($value)) {
            return $value ? 'Да' : 'Нет';
        }

        if (is_string($value) || is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return '-';
    }
}
