<?php

declare(strict_types=1);

namespace App\Auth\Http\DeleteProfileAddress;

use App\Auth\Application\UseCases\DeleteProfileAddress\DeleteProfileAddressHandler;
use App\Auth\Application\UseCases\DeleteProfileAddress\DeleteProfileAddressInput;

class DeleteProfileAddressController
{
    public function __invoke(
        DeleteProfileAddressRequest $request,
        DeleteProfileAddressHandler $handler,
    ): DeleteProfileAddressResponse {
        $result = $handler->execute(DeleteProfileAddressInput::from($request->inputData()));

        return DeleteProfileAddressResponse::make($result);
    }
}
