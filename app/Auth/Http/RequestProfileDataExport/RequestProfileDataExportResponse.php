<?php

declare(strict_types=1);

namespace App\Auth\Http\RequestProfileDataExport;

use App\Auth\Application\UseCases\RequestProfileDataExport\RequestProfileDataExportOutput;
use App\Shared\Http\Resources\OutputResource;
use Illuminate\Http\Request;

/**
 * @mixin RequestProfileDataExportOutput
 */
class RequestProfileDataExportResponse extends OutputResource
{
    /**
     * @return array{
     *     requested: bool,
     *     message: string
     * }
     * @phpstan-return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
