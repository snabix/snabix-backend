<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCases\DeleteCategoryAttributeDefinition;

use App\Catalog\Domain\Contracts\CategoryAttributeDefinitionRepositoryInterface;
use App\Catalog\Infrastructure\Models\EloquentCategoryAttributeDefinition;
use Illuminate\Database\Eloquent\ModelNotFoundException;

readonly class DeleteCategoryAttributeDefinitionHandler
{
    public function __construct(
        private CategoryAttributeDefinitionRepositoryInterface $repository,
    ) {}

    public function execute(DeleteCategoryAttributeDefinitionInput $input): DeleteCategoryAttributeDefinitionOutput
    {
        $definition = $this->repository->findById($input->attributeDefinitionId);

        if ($definition === null) {
            throw (new ModelNotFoundException())->setModel(EloquentCategoryAttributeDefinition::class, [$input->attributeDefinitionId]);
        }

        $this->repository->delete($definition);

        return DeleteCategoryAttributeDefinitionOutput::from([
            'deleted' => true,
        ]);
    }
}
