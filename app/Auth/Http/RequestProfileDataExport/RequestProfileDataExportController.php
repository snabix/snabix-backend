<?php

declare(strict_types=1);

namespace App\Auth\Http\RequestProfileDataExport;

use App\Auth\Application\UseCases\RequestProfileDataExport\RequestProfileDataExportHandler;
use App\Auth\Application\UseCases\RequestProfileDataExport\RequestProfileDataExportInput;
use App\Auth\Infrastructure\Exceptions\NotFoundException;

class RequestProfileDataExportController
{
    /**
     * @throws NotFoundException
     */
    public function __invoke(
        RequestProfileDataExportRequest $request,
        RequestProfileDataExportHandler $handler,
    ): RequestProfileDataExportResponse {
        $result = $handler->execute(RequestProfileDataExportInput::from($request->inputData()));

        return RequestProfileDataExportResponse::make($result);
    }
}
