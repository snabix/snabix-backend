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

        $this->mailSender->sendWithAttachments(
            new Email($user->email),
            'Ваши данные аккаунта SNABIX',
            'emails.profile-data-export',
            [
                'accountLabel' => trim($user->first_name . ' ' . $user->last_name) ?: $user->email,
                'requestedAt'  => now()->timezone($this->appTimezone())->format('d.m.Y H:i:s T'),
            ],
            [
                [
                    'filename' => 'snabix-profile-data.json',
                    'contents' => $this->profileJson($user),
                    'mime'     => 'application/json',
                ],
            ],
        );

        return RequestProfileDataExportOutput::from([
            'requested' => true,
            'message'   => 'Запрос отправлен. Письмо с данными профиля придет на email аккаунта.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function profilePayload(EloquentUser $user): array
    {
        return [
            'generatedAt' => now()->timezone($this->appTimezone())->toIso8601String(),
            'profile'     => [
                'id'        => $user->id,
                'firstName' => $user->first_name,
                'lastName'  => $user->last_name,
                'aboutMe'   => $user->about,
                'isActive'  => $user->is_active,
                'createdAt' => $user->created_at->format(DATE_ATOM),
                'updatedAt' => $user->updated_at->format(DATE_ATOM),
            ],
            'contacts'    => [
                'email'           => $user->email,
                'emailVerified'   => $user->email_verified_at !== null,
                'emailVerifiedAt' => $user->email_verified_at?->format(DATE_ATOM),
                'phoneNumber'     => $user->phone_number,
            ],
            'security'    => [
                'password' => 'not_exported',
                'note'     => 'Пароль не экспортируется. В системе хранится только защищенный хеш.',
            ],
            'addresses'   => $this->addressPayload($user),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function addressPayload(EloquentUser $user): array
    {
        if ($user->addresses->isEmpty()) {
            return [];
        }

        $addresses = [];

        foreach ($user->addresses->values() as $address) {
            $addresses[] = [
                'id'          => $address->id,
                'label'       => $address->label,
                'addressLine' => $address->address_line,
                'isPrimary'   => $address->is_primary,
                'region'      => [
                    'id'       => $address->region->id,
                    'name'     => $address->region->name,
                    'fullName' => $address->region->fullname ?? $address->region->name,
                    'label'    => $address->region->label,
                ],
                'city'        => $address->city !== null
                    ? [
                        'id'    => $address->city->id,
                        'name'  => $address->city->name,
                        'label' => $address->city->label,
                    ]
                    : null,
            ];
        }

        return $addresses;
    }

    private function profileJson(EloquentUser $user): string
    {
        $json = json_encode(
            $this->profilePayload($user),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        );

        return is_string($json) ? $json : '{}';
    }

    private function appTimezone(): string
    {
        $timezone = config('app.timezone');

        return is_string($timezone) && $timezone !== '' ? $timezone : 'UTC';
    }
}
