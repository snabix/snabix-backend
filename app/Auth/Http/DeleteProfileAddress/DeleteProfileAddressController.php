<?php

declare(strict_types=1);

namespace App\Auth\Http\DeleteProfileAddress;

use App\Auth\Application\Services\UserAddressService;

class DeleteProfileAddressController
{
    public function __invoke(
        DeleteProfileAddressRequest $request,
        UserAddressService $userAddressService,
        string $addressId,
    ): DeleteProfileAddressResponse {
        $userAddressService->delete(
            $request->userId(),
            $addressId,
        );

        return DeleteProfileAddressResponse::make([]);
    }
}
