<?php

declare(strict_types=1);

namespace App\Listing\Http\UpdateListing;

use App\Listing\Application\UseCases\UpdateListing\UpdateListingHandler;
use App\Listing\Application\UseCases\UpdateListing\UpdateListingInput;
use Illuminate\Validation\ValidationException;

class UpdateListingController
{
    /**
     * @throws ValidationException
     */
    public function __invoke(
        UpdateListingRequest $request,
        UpdateListingHandler $handler,
    ): UpdateListingResponse {
        $request->validated();
        $condition  = $request->input('condition');
        $price      = $request->input('price');
        $currency   = $request->input('currency');

        $result     = $handler->execute(
            UpdateListingInput::from([
                'userId'         => $request->userId(),
                'listingId'      => $request->listingId(),
                'categoryId'     => $request->integer('categoryId'),
                'type'           => $request->integer('type'),
                'condition'      => is_int($condition) ? $condition : (is_numeric($condition) ? (int) $condition : null),
                'title'          => $request->string('title')->toString(),
                'description'    => $request->string('description')->toString(),
                'price'          => is_int($price) ? $price : (is_numeric($price) ? (int) $price : null),
                'currency'       => is_string($currency) && $currency !== '' ? mb_strtoupper($currency) : null,
                'isNegotiable'   => $request->boolean('isNegotiable', false),
                'contactName'    => $request->filled('contactName') ? $request->string('contactName')->toString() : null,
                'contactPhone'   => $request->filled('contactPhone') ? $request->string('contactPhone')->toString() : null,
                'contactEmail'   => $request->filled('contactEmail') ? $request->string('contactEmail')->toString() : null,
                'attributeValues'=> $request->attributeValues(),
            ]),
        );

        return UpdateListingResponse::make($result);
    }
}
