<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\RequestProfileDataExport;

use App\Auth\Infrastructure\Exceptions\NotFoundException;
use App\Auth\Infrastructure\Models\EloquentUser;
use App\Mail\Application\Contracts\MailSender;
use App\Shared\Domain\ValueObjects\Email;

readonly class RequestProfileDataExportHandler
{
    public function __construct(
        private MailSender $mailSender,
    ) {}

    /**
     * @throws NotFoundException
     */
    public function execute(RequestProfileDataExportInput $data): RequestProfileDataExportOutput
    {
        $user = EloquentUser::query()
            ->with(['addresses.region', 'addresses.city'])
            ->find($data->userId);

        if (! $user instanceof EloquentUser) {
            throw new NotFoundException('Пользователь не найден.');
        }

        $this->mailSender->send(
            new Email($user->email),
            'Ваши данные аккаунта SNABIX',
            'emails.profile-data-export',
            [
                'accountLabel' => trim($user->first_name . ' ' . $user->last_name) ?: $user->email,
                'requestedAt'  => now()->timezone($this->appTimezone())->format('d.m.Y H:i:s T'),
                'sections'     => $this->profileSections($user),
            ],
        );

        return RequestProfileDataExportOutput::from([
            'requested' => true,
            'message'   => 'Запрос отправлен. Письмо с данными профиля придет на email аккаунта.',
        ]);
    }

    /**
     * @return list<array{title: string, rows: list<array{label: string, value: string}>}>
     */
    private function profileSections(EloquentUser $user): array
    {
        return [
            [
                'title' => 'Профиль',
                'rows'  => [
                    ['label' => 'ID аккаунта', 'value' => $user->id],
                    ['label' => 'Имя', 'value' => $user->first_name],
                    ['label' => 'Фамилия', 'value' => $user->last_name],
                    ['label' => 'Статус аккаунта', 'value' => $user->is_active ? 'Активен' : 'Отключен'],
                    ['label' => 'Дата регистрации', 'value' => $user->created_at->format(DATE_ATOM)],
                    ['label' => 'Последнее обновление', 'value' => $user->updated_at->format(DATE_ATOM)],
                ],
            ],
            [
                'title' => 'Контакты',
                'rows'  => [
                    ['label' => 'Email', 'value' => $user->email],
                    ['label' => 'Email подтвержден', 'value' => $user->email_verified_at !== null ? 'Да' : 'Нет'],
                    ['label' => 'Дата подтверждения email', 'value' => $user->email_verified_at?->format(DATE_ATOM) ?? 'Не указано'],
                    ['label' => 'Телефон', 'value' => $user->phone_number ?: 'Не указан'],
                ],
            ],
            [
                'title' => 'Безопасность',
                'rows'  => [
                    ['label' => 'Пароль', 'value' => 'Не экспортируется. Хранится только защищенный хеш.'],
                ],
            ],
            [
                'title' => 'Адреса профиля',
                'rows'  => $this->addressRows($user),
            ],
        ];
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    private function addressRows(EloquentUser $user): array
    {
        if ($user->addresses->isEmpty()) {
            return [
                ['label' => 'Адреса', 'value' => 'Не указаны'],
            ];
        }

        $rows = [];

        foreach ($user->addresses->values() as $index => $address) {
            $parts  = array_filter([
                $address->label,
                $address->region->label ?? $address->region->name,
                $address->city !== null ? ($address->city->label ?? $address->city->name) : null,
                $address->address_line,
                $address->is_primary ? 'Основной' : null,
            ]);

            $rows[] = [
                'label' => 'Адрес ' . ($index + 1),
                'value' => implode(', ', $parts) ?: 'Не указано',
            ];
        }

        return $rows;
    }

    private function appTimezone(): string
    {
        $timezone = config('app.timezone');

        return is_string($timezone) && $timezone !== '' ? $timezone : 'UTC';
    }
}
