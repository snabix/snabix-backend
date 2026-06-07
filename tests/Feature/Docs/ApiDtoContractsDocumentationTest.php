<?php

declare(strict_types=1);

namespace Tests\Feature\Docs;

use Tests\TestCase;

class ApiDtoContractsDocumentationTest extends TestCase
{
    public function test_api_dto_contracts_document_enum_values_and_labels(): void
    {
        $contents = $this->contractsDocumentation();

        $this->assertStringContainsString('| PRODUCT | 1 | Товар |', $contents);
        $this->assertStringContainsString('| SERVICE | 2 | Услуга |', $contents);
        $this->assertStringContainsString('| PENDING_REVIEW | 2 | На проверке |', $contents);
        $this->assertStringContainsString('| PUBLISHED | 3 | Опубликовано |', $contents);
        $this->assertStringContainsString('| NOT_APPLICABLE | 3 | Не применяется |', $contents);
        $this->assertStringContainsString('| MULTISELECT | 5 | Выбор нескольких значений |', $contents);
    }

    public function test_api_dto_contracts_document_private_and_public_listing_boundaries(): void
    {
        $contents = $this->contractsDocumentation();

        $this->assertStringContainsString('## Private Listing DTO', $contents);
        $this->assertStringContainsString('## Public Listing DTO', $contents);
        $this->assertStringContainsString('Private-only поля: `userId`, `contactName`, `contactPhone`, `contactEmail`, `rejectionReason`.', $contents);
        $this->assertStringContainsString('Поля `userId`, `contactName`, `contactPhone`, `contactEmail`, `rejectionReason` в public DTO не возвращаются.', $contents);
    }

    public function test_api_dto_contracts_document_complex_response_examples(): void
    {
        $contents = $this->contractsDocumentation();

        $this->assertStringContainsString('"items": [', $contents);
        $this->assertStringContainsString('"meta": {', $contents);
        $this->assertStringContainsString('"catalogTypeLabel": "Товары"', $contents);
        $this->assertStringContainsString('"typeLabel": "Товар"', $contents);
        $this->assertStringContainsString('"statusLabel": "Опубликовано"', $contents);
        $this->assertStringContainsString('"conditionLabel": "Б/у"', $contents);
        $this->assertStringContainsString('"typeLabel": "Выбор одного значения"', $contents);
        $this->assertStringContainsString('"showInCard": true', $contents);
    }

    private function contractsDocumentation(): string
    {
        $path     = base_path('docs/API_DTO_CONTRACTS.md');

        $this->assertFileExists($path);

        $contents = file_get_contents($path);

        $this->assertIsString($contents);

        return $contents;
    }
}
