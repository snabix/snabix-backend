<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Http\Resources;

use App\Auth\Application\UseCases\SignIn\SignInOutput;
use App\Auth\Http\SignIn\SignInResponse;
use App\Catalog\Application\UseCases\ListRootCategories\ListRootCategoriesOutput;
use App\Catalog\Http\ListRootCategories\ListRootCategoriesResponse;
use App\Listing\Application\UseCases\CreateListing\CreateListingOutput;
use App\Listing\Http\CreateListing\CreateListingResponse;
use Illuminate\Http\Request;
use LogicException;
use Tests\TestCase;
use UnexpectedValueException;

class OutputResourceTest extends TestCase
{
    public function test_output_resource_serializes_the_complete_output(): void
    {
        $response = new SignInResponse(new SignInOutput(userId: 'user-id'));

        $this->assertSame(
            ['userId' => 'user-id'],
            $response->toArray(Request::create('/')),
        );
    }

    public function test_item_output_resource_unwraps_the_item_property(): void
    {
        $response = new CreateListingResponse(new CreateListingOutput(
            item: ['id' => 'listing-id', 'title' => 'Listing'],
        ));

        $this->assertSame(
            ['id' => 'listing-id', 'title' => 'Listing'],
            $response->toArray(Request::create('/')),
        );
    }

    public function test_items_output_resource_unwraps_the_items_property(): void
    {
        $items    = [
            ['id' => 'category-id', 'name' => 'Category'],
        ];
        $response = new ListRootCategoriesResponse(new ListRootCategoriesOutput($items));

        $this->assertSame($items, $response->toArray(Request::create('/')));
    }

    public function test_output_resource_rejects_non_data_resources(): void
    {
        $response = new SignInResponse(['userId' => 'user-id']);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('expects a Spatie transformable data object');

        $response->toArray(Request::create('/'));
    }

    public function test_item_output_resource_rejects_an_output_without_an_item_array(): void
    {
        $response = new CreateListingResponse(new SignInOutput(userId: 'user-id'));

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('expects an array in the output item property');

        $response->toArray(Request::create('/'));
    }
}
