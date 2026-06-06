<?php

declare(strict_types=1);

namespace App\News\Http\ShowPublishedNewsPost;

use App\News\Application\UseCases\ShowPublishedNewsPost\ShowPublishedNewsPostHandler;
use App\News\Application\UseCases\ShowPublishedNewsPost\ShowPublishedNewsPostInput;

class ShowPublishedNewsPostController
{
    public function __invoke(
        ShowPublishedNewsPostRequest $request,
        ShowPublishedNewsPostHandler $handler,
    ): ShowPublishedNewsPostResponse {
        $result = $handler->execute(ShowPublishedNewsPostInput::from($request->inputData()));

        return ShowPublishedNewsPostResponse::make($result);
    }
}
