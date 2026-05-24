<?php

declare(strict_types=1);

namespace App\Auth\Http\ReplaceProfileAddresses;

use App\Auth\Application\UseCases\ReplaceProfileAddresses\ReplaceProfileAddressesHandler;
use App\Auth\Application\UseCases\ReplaceProfileAddresses\ReplaceProfileAddressesInput;
use Illuminate\Validation\ValidationException;
use Throwable;

class ReplaceProfileAddressesController
{
    /**
     * @throws ValidationException|Throwable
     */
    public function __invoke(
        ReplaceProfileAddressesRequest $request,
        ReplaceProfileAddressesHandler $handler,
    ): ReplaceProfileAddressesResponse {
        $result = $handler->execute(ReplaceProfileAddressesInput::from($request->inputData()));

        return ReplaceProfileAddressesResponse::make($result);
    }
}
