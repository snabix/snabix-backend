<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\ReplaceProfileAddresses;

use App\Auth\Application\Services\UserAddressService;
use Illuminate\Validation\ValidationException;
use Throwable;

readonly class ReplaceProfileAddressesHandler
{
    public function __construct(
        private UserAddressService $userAddressService,
    ) {}

    /**
     * @throws ValidationException|Throwable
     */
    public function execute(ReplaceProfileAddressesInput $data): ReplaceProfileAddressesOutput
    {
        return ReplaceProfileAddressesOutput::from([
            'addresses' => $this->userAddressService->replace(
                $data->userId,
                $data->addresses,
            ),
        ]);
    }
}
