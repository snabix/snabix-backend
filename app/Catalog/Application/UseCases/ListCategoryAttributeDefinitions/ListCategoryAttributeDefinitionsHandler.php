<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCases\ListCategoryAttributeDefinitions;

use App\Catalog\Application\Support\CategoryAttributeDefinitionPayloadMapper;
use App\Catalog\Domain\Contracts\CategoryAttributeDefinitionRepositoryInterface;
use App\Catalog\Infrastructure\Models\EloquentCategoryAttributeDefinition;

readonly class ListCategoryAttributeDefinitionsHandler
{
    public function __construct(
        private CategoryAttributeDefinitionRepositoryInterface $repository,
        private CategoryAttributeDefinitionPayloadMapper $payloadMapper,
    ) {}

    public function execute(ListCategoryAttributeDefinitionsInput $input): ListCategoryAttributeDefinitionsOutput
    {
        $items = $this->repository
            ->list($input->onlyActive)
            ->map(fn(EloquentCategoryAttributeDefinition $definition): array => $this->payloadMapper->map($definition))
            ->values()
            ->all();

        return ListCategoryAttributeDefinitionsOutput::from([
            'items' => $items,
        ]);
    }
}
