<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\SignUp;

use App\Auth\Domain\Contracts\UserRepositoryInterface;
use App\Auth\Domain\Entities\User;
use App\Auth\Domain\Events\UserRegistered;
use App\Auth\Domain\ValueObjects\FirstName;
use App\Auth\Domain\ValueObjects\LastName;
use App\Auth\Domain\ValueObjects\PhoneNumber;
use App\Shared\Application\DTO\IdempotencyResult;
use App\Shared\Domain\Contracts\HasherInterface;
use App\Shared\Domain\Contracts\SessionAuthenticatorInterface;
use App\Shared\Domain\Exceptions\IdempotencyConflictException;
use App\Shared\Domain\ValueObjects\Email;
use App\Shared\Domain\ValueObjects\Password;
use App\Shared\Domain\ValueObjects\UUID;
use App\Shared\Infrastructure\Database\UniqueConstraintViolationDetector;
use App\Shared\Infrastructure\Services\IdempotencyService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

readonly class SignUpHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private HasherInterface $hasherService,
        private SessionAuthenticatorInterface $sessionAuthenticator,
        private IdempotencyService $idempotencyService,
        private UniqueConstraintViolationDetector $uniqueConstraintViolationDetector,
    ) {}

    /**
     * @throws Throwable
     */
    public function execute(
        SignUpInput $data,
    ): SignUpOutput {
        $email = new Email($data->email);

        try {
            $result = $this->idempotencyService->execute(
                idempotencyKey: $data->idempotencyKey,
                scope: 'auth.sign-up',
                actorKey: $email->value(),
                payload: [
                    'firstName'   => $data->firstName,
                    'lastName'    => $data->lastName,
                    'email'       => $email->value(),
                    'phoneNumber' => $data->phoneNumber,
                    'password'    => $data->password,
                ],
                operation: function () use ($data, $email): IdempotencyResult {
                    if ($this->userRepository->existByEmail($email)) {
                        $this->throwEmailAlreadyRegistered();
                    }

                    $domainUser = new User(
                        id: UUID::generate(),
                        firstName: new FirstName($data->firstName ?: 'User'),
                        lastName: new LastName($data->lastName ?: 'Account'),
                        email: $email,
                        password: new Password(
                            $this->hasherService->hash($data->password),
                        ),
                        phoneNumber: filled($data->phoneNumber)
                            ? new PhoneNumber($data->phoneNumber)
                            : null,
                    );

                    $output     = SignUpOutput::from([
                        'userId' => $domainUser->id->value(),
                    ]);

                    /** @var SignUpOutput $output */
                    $this->userRepository->save($domainUser);

                    DB::afterCommit(
                        fn(): ?array => event(new UserRegistered($domainUser)),
                    );

                    return new IdempotencyResult(
                        resourceId: $domainUser->id->value(),
                        value: $output,
                    );
                },
                replay: function (string $userId): SignUpOutput {
                    if ($this->userRepository->byId(new UUID($userId)) === null) {
                        throw new IdempotencyConflictException();
                    }

                    return SignUpOutput::from(['userId' => $userId]);
                },
            );
        } catch (UniqueConstraintViolationException $exception) {
            if (! $this->uniqueConstraintViolationDetector->matches($exception, 'users_email_unique')) {
                throw $exception;
            }

            $this->throwEmailAlreadyRegistered();
        }

        $this->sessionAuthenticator->login($result->userId);

        return $result;
    }

    private function throwEmailAlreadyRegistered(): never
    {
        throw ValidationException::withMessages([
            'email' => ['Пользователь с таким email уже существует.'],
        ]);
    }
}
