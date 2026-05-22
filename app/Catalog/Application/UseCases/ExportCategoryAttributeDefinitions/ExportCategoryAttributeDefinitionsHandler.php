<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCases\ExportCategoryAttributeDefinitions;

use App\Catalog\Application\Support\CategoryAttributeDefinitionPayloadMapper;
use App\Catalog\Domain\Contracts\CategoryAttributeDefinitionRepositoryInterface;
use App\Catalog\Infrastructure\Models\EloquentCategoryAttributeDefinition;

readonly class ExportCategoryAttributeDefinitionsHandler
{
    public function __construct(
        private CategoryAttributeDefinitionRepositoryInterface $repository,
        private CategoryAttributeDefinitionPayloadMapper $payloadMapper,
    ) {}

    public function execute(ExportCategoryAttributeDefinitionsInput $input): ExportCategoryAttributeDefinitionsOutput
    {
        $items = $this->repository
            ->list($input->onlyActive)
            ->map(fn(EloquentCategoryAttributeDefinition $definition): array => $this->payloadMapper->map($definition))
            ->values()
            ->all();

        return ExportCategoryAttributeDefinitionsOutput::from([
            'items' => $items,
            'meta'  => [
                'schemaVersion'  => 1,
                'exportedAt'     => now()->toIso8601String(),
                'total'          => count($items),
            ],
        ]);
    }
}
