<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\DeleteProfileAddress;

use App\Auth\Application\Services\UserAddressService;

readonly class DeleteProfileAddressHandler
{
    public function __construct(
        private UserAddressService $userAddressService,
    ) {}

    public function execute(DeleteProfileAddressInput $data): DeleteProfileAddressOutput
    {
        $this->userAddressService->delete($data->userId, $data->addressId);

        return DeleteProfileAddressOutput::from([
            'deleted' => true,
        ]);
    }
}
