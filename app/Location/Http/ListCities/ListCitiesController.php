<?php

declare(strict_types=1);

namespace App\Location\Http\ListCities;

use App\Location\Application\UseCases\ListCities\ListCitiesHandler;
use App\Location\Application\UseCases\ListCities\ListCitiesInput;

class ListCitiesController
{
    public function __invoke(
        ListCitiesRequest $request,
        ListCitiesHandler $handler,
    ): ListCitiesResponse {
        $result = $handler->execute(ListCitiesInput::from($request->inputData()));

        return ListCitiesResponse::make($result);
    }
}
