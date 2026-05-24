<?php

declare(strict_types=1);

namespace App\Auth\Http\ListProfileAddresses;

use App\Auth\Application\UseCases\ListProfileAddresses\ListProfileAddressesHandler;
use App\Auth\Application\UseCases\ListProfileAddresses\ListProfileAddressesInput;

class ListProfileAddressesController
{
    public function __invoke(
        ListProfileAddressesRequest $request,
        ListProfileAddressesHandler $handler,
    ): ListProfileAddressesResponse {
        $result = $handler->execute(ListProfileAddressesInput::from($request->inputData()));

        return ListProfileAddressesResponse::make($result);
    }
}
