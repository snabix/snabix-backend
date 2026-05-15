<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCases\ShowCategoryAttributeDefinition;

use App\Catalog\Application\Support\CategoryAttributeDefinitionPayloadMapper;
use App\Catalog\Domain\Contracts\CategoryAttributeDefinitionRepositoryInterface;
use App\Catalog\Infrastructure\Models\EloquentCategoryAttributeDefinition;
use Illuminate\Database\Eloquent\ModelNotFoundException;

readonly class ShowCategoryAttributeDefinitionHandler
{
    public function __construct(
        private CategoryAttributeDefinitionRepositoryInterface $repository,
        private CategoryAttributeDefinitionPayloadMapper $payloadMapper,
    ) {}

    public function execute(ShowCategoryAttributeDefinitionInput $input): ShowCategoryAttributeDefinitionOutput
    {
        $definition = $this->repository->findById($input->attributeDefinitionId);

        if ($definition === null) {
            throw (new ModelNotFoundException())->setModel(EloquentCategoryAttributeDefinition::class, [$input->attributeDefinitionId]);
        }

        return ShowCategoryAttributeDefinitionOutput::from([
            'item' => $this->payloadMapper->map($definition),
        ]);
    }
}
