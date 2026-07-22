<?php

declare(strict_types=1);

namespace Tests\Feature\Docs;

use Tests\TestCase;

class ApiDtoContractsDocumentationTest extends TestCase
{
    public function test_api_dto_contracts_document_enum_values_and_labels(): void
    {
        $contents = $this->contractsDocumentation();

        $this->assertStringContainsString('| `listingKind` | `product`, `service` | `1`, `2` |', $contents);
        $this->assertStringContainsString('`pendingReview`', $contents);
        $this->assertStringContainsString('`notApplicable`', $contents);
        $this->assertStringContainsString('`multiSelect`', $contents);
        $this->assertStringContainsString('`publicationStatus`', $contents);
        $this->assertStringContainsString('`reviewStatus`', $contents);
    }

    public function test_api_dto_contracts_document_field_naming_and_deprecation_policy(): void
    {
        $contents = $this->contractsDocumentation();

        $this->assertStringContainsString('## Conventions имен полей', $contents);
        $this->assertStringContainsString('`priceAmountMinor: 85000`, `priceCurrency: "RUB"`', $contents);
        $this->assertStringContainsString('`...At`, ISO 8601 с timezone', $contents);
        $this->assertStringContainsString('Общие поля `type` и `status` запрещены для новых resource DTO.', $contents);
        $this->assertStringContainsString('заканчивается **2026-10-31**', $contents);
        $this->assertStringContainsString('frontend разворачивается первым', $contents);
    }

    public function test_api_dto_contracts_document_private_and_public_listing_boundaries(): void
    {
        $contents = $this->contractsDocumentation();

        $this->assertStringContainsString('## Private Listing DTO', $contents);
        $this->assertStringContainsString('## Public Listing DTO', $contents);
        $this->assertStringContainsString('Private-only поля: `userId`, `contactName`, `contactPhone`, `contactEmail`, `rejectionReason`, `media`.', $contents);
        $this->assertStringContainsString('Поля `userId`, `contactName`, `contactPhone`, `contactEmail`, `rejectionReason`, `media` в public DTO не возвращаются.', $contents);
    }

    public function test_api_dto_contracts_document_complex_response_examples(): void
    {
        $contents = $this->contractsDocumentation();

        $this->assertStringContainsString('"items": [', $contents);
        $this->assertStringContainsString('"meta": {', $contents);
        $this->assertStringContainsString('"catalogKind": "product"', $contents);
        $this->assertStringContainsString('"listingKind": "product"', $contents);
        $this->assertStringContainsString('"listingStatus": "published"', $contents);
        $this->assertStringContainsString('"itemCondition": "used"', $contents);
        $this->assertStringContainsString('"valueType": "select"', $contents);
        $this->assertStringContainsString('"showInCard": true', $contents);
    }

    private function contractsDocumentation(): string
    {
        $path     = base_path('.docs/API_DTO_CONTRACTS.md');

        $this->assertFileExists($path);

        $contents = file_get_contents($path);

        $this->assertIsString($contents);

        return $contents;
    }
}
