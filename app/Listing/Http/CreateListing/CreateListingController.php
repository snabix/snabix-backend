<?php

declare(strict_types=1);

namespace App\Listing\Http\CreateListing;

use App\Listing\Application\UseCases\CreateListing\CreateListingHandler;
use App\Listing\Application\UseCases\CreateListing\CreateListingInput;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CreateListingController
{
    /**
     * @throws ValidationException
     */
    public function __invoke(
        CreateListingRequest $request,
        CreateListingHandler $handler,
    ): JsonResponse {
        $request->validated();
        $user       = $request->user();
        $identifier = is_object($user) ? $user->getAuthIdentifier() : null;
        $userId     = is_string($identifier) || is_int($identifier)
            ? (string) $identifier
            : '';
        $condition  = $request->input('condition');
        $price      = $request->input('price');
        $currency   = $request->input('currency');
        $attributes = $request->input('attributeValues');

        $result     = $handler->execute(
            CreateListingInput::from([
                'userId'         => $userId,
                'categoryId'     => $request->integer('categoryId'),
                'type'           => $request->integer('type'),
                'status'         => $request->integer('status'),
                'condition'      => is_int($condition) ? $condition : (is_numeric($condition) ? (int) $condition : null),
                'title'          => $request->string('title')->toString(),
                'description'    => $request->string('description')->toString(),
                'price'          => is_int($price) ? $price : (is_numeric($price) ? (int) $price : null),
                'currency'       => is_string($currency) && $currency !== '' ? mb_strtoupper($currency) : null,
                'isNegotiable'   => $request->boolean('isNegotiable', false),
                'contactName'    => $request->filled('contactName') ? $request->string('contactName')->toString() : null,
                'contactPhone'   => $request->filled('contactPhone') ? $request->string('contactPhone')->toString() : null,
                'contactEmail'   => $request->filled('contactEmail') ? $request->string('contactEmail')->toString() : null,
                'isFeatured'     => $request->boolean('isFeatured', false),
                'rejectionReason'=> $request->filled('rejectionReason') ? $request->string('rejectionReason')->toString() : null,
                'attributeValues'=> is_array($attributes) ? $attributes : [],
            ]),
        );

        return CreateListingResponse::make($result)
            ->response()
            ->setStatusCode(201);
    }
}
