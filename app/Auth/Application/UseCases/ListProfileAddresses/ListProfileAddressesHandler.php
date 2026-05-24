<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\ListProfileAddresses;

use App\Auth\Application\Services\UserAddressService;

readonly class ListProfileAddressesHandler
{
    public function __construct(
        private UserAddressService $userAddressService,
    ) {}

    public function execute(ListProfileAddressesInput $data): ListProfileAddressesOutput
    {
        return ListProfileAddressesOutput::from([
            'addresses' => $this->userAddressService->listPayload($data->userId),
        ]);
    }
}
