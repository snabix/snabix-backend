<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\SignUp;

use App\Auth\Domain\Contracts\UserRepositoryInterface;
use App\Auth\Domain\Entities\User;
use App\Auth\Domain\Events\UserRegistered;
use App\Auth\Domain\ValueObjects\FirstName;
use App\Auth\Domain\ValueObjects\LastName;
use App\Auth\Domain\ValueObjects\PhoneNumber;
use App\Shared\Domain\Contracts\HasherInterface;
use App\Shared\Domain\Contracts\SessionAuthenticatorInterface;
use App\Shared\Domain\ValueObjects\Email;
use App\Shared\Domain\ValueObjects\Password;
use App\Shared\Domain\ValueObjects\UUID;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

readonly class SignUpHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private HasherInterface $hasherService,
        private SessionAuthenticatorInterface $sessionAuthenticator,
    ) {}

    /**
     * @throws Throwable
     */
    public function execute(
        SignUpInput $data,
    ): SignUpOutput {
        $email      = new Email($data->email);

        if ($this->userRepository->existByEmail($email)) {
            throw ValidationException::withMessages([
                'email' => ['Пользователь с таким email уже существует.'],
            ]);
        }

        $domainUser = new User(
            id: UUID::generate(),
            firstName: new FirstName($data->firstName),
            lastName: new LastName($data->lastName),
            email: new Email($data->email),
            password: new Password(
                $this->hasherService->hash(
                    $data->password,
                ),
            ),
            phoneNumber: filled($data->phoneNumber) ? new PhoneNumber($data->phoneNumber) : null,
        );

        /** @var SignUpOutput $result */
        $result     = DB::transaction(
            function () use ($domainUser): SignUpOutput {
                $this->userRepository->save($domainUser);

                DB::afterCommit(
                    fn(): ?array => event(new UserRegistered($domainUser)),
                );

                $this->sessionAuthenticator->login(
                    $domainUser->id->value(),
                );

                return SignUpOutput::from([
                    'userId' => $domainUser->id->value(),
                ]);
            },
        );

        return $result;
    }
}
