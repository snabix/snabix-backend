<?php

declare(strict_types=1);

namespace App\News\Http\ListPublishedNewsPosts;

use App\News\Application\UseCases\ListPublishedNewsPosts\ListPublishedNewsPostsHandler;
use App\News\Application\UseCases\ListPublishedNewsPosts\ListPublishedNewsPostsInput;

class ListPublishedNewsPostsController
{
    public function __invoke(
        ListPublishedNewsPostsRequest $request,
        ListPublishedNewsPostsHandler $handler,
    ): ListPublishedNewsPostsResponse {
        $result = $handler->execute(ListPublishedNewsPostsInput::from($request->inputData()));

        return ListPublishedNewsPostsResponse::make($result);
    }
}
