<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Auth\Infrastructure\Models\EloquentUserAddress;
use App\Location\Infrastructure\Models\EloquentCity;
use App\Location\Infrastructure\Models\EloquentRegion;
use Tests\Feature\FeatureTestCase;

class ProfileAddressesTest extends FeatureTestCase
{
    public function test_authenticated_user_can_replace_profile_addresses(): void
    {
        $user          = EloquentUser::factory()->create();
        $bashkortostan = $this->createRegion('0200000000000', 'Республика Башкортостан');
        $tatarstan     = $this->createRegion('1600000000000', 'Республика Татарстан');
        $ufa           = $this->createCity($bashkortostan, '0200000100000', 'Уфа');
        $kazan         = $this->createCity($tatarstan, '1600000100000', 'Казань');

        $this
            ->actingAs($user)
            ->putJson('/api/v1/auth/me/addresses', [
                'addresses' => [
                    [
                        'regionId'    => $bashkortostan->id,
                        'cityId'      => $ufa->id,
                        'label'       => 'Дом',
                        'addressLine' => 'Проспект Октября',
                        'isPrimary'   => true,
                    ],
                    [
                        'regionId'  => $tatarstan->id,
                        'cityId'    => $kazan->id,
                        'label'     => 'Работа',
                        'isPrimary' => false,
                    ],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.addresses.0.label', 'Дом')
            ->assertJsonPath('data.addresses.0.region.name', 'Республика Башкортостан')
            ->assertJsonPath('data.addresses.0.city.name', 'Уфа')
            ->assertJsonPath('data.addresses.0.isPrimary', true)
            ->assertJsonPath('data.addresses.1.label', 'Работа');

        $this->assertDatabaseHas('user_addresses', [
            'user_id'      => $user->id,
            'region_id'    => $bashkortostan->id,
            'city_id'      => $ufa->id,
            'label'        => 'Дом',
            'address_line' => 'Проспект Октября',
            'is_primary'   => true,
        ]);

        $this
            ->actingAs($user)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.addresses.0.label', 'Дом')
            ->assertJsonPath('data.addresses.1.city.name', 'Казань');
    }

    public function test_city_must_belong_to_selected_region(): void
    {
        $user          = EloquentUser::factory()->create();
        $bashkortostan = $this->createRegion('0200000000000', 'Республика Башкортостан');
        $tatarstan     = $this->createRegion('1600000000000', 'Республика Татарстан');
        $kazan         = $this->createCity($tatarstan, '1600000100000', 'Казань');

        $this
            ->actingAs($user)
            ->putJson('/api/v1/auth/me/addresses', [
                'addresses' => [
                    [
                        'regionId' => $bashkortostan->id,
                        'cityId'   => $kazan->id,
                    ],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('addresses.0.cityId');
    }

    public function test_deleting_primary_address_assigns_next_primary_address(): void
    {
        $user        = EloquentUser::factory()->create();
        $region      = $this->createRegion('0200000000000', 'Республика Башкортостан');
        $ufa         = $this->createCity($region, '0200000100000', 'Уфа');
        $sterlitamak = $this->createCity($region, '0200000200000', 'Стерлитамак');

        $primary     = EloquentUserAddress::query()->create([
            'user_id'    => $user->id,
            'region_id'  => $region->id,
            'city_id'    => $ufa->id,
            'label'      => 'Дом',
            'is_primary' => true,
            'sort_order' => 0,
        ]);
        $secondary   = EloquentUserAddress::query()->create([
            'user_id'    => $user->id,
            'region_id'  => $region->id,
            'city_id'    => $sterlitamak->id,
            'label'      => 'Склад',
            'is_primary' => false,
            'sort_order' => 1,
        ]);

        $this
            ->actingAs($user)
            ->deleteJson('/api/v1/auth/me/addresses/' . $primary->id)
            ->assertOk()
            ->assertJsonPath('data.deleted', true);

        $this->assertDatabaseMissing('user_addresses', [
            'id' => $primary->id,
        ]);
        $this->assertDatabaseHas('user_addresses', [
            'id'         => $secondary->id,
            'is_primary' => true,
        ]);
    }

    public function test_locations_api_returns_regions_and_cities(): void
    {
        $region = $this->createRegion('0200000000000', 'Республика Башкортостан');
        $city   = $this->createCity($region, '0200000100000', 'Уфа');

        $this
            ->getJson('/api/v1/locations/regions')
            ->assertOk()
            ->assertJsonPath('data.regions.0.id', $region->id)
            ->assertJsonPath('data.regions.0.name', 'Республика Башкортостан')
            ->assertJsonPath('data.regions.0.fullName', 'Республика Башкортостан');

        $this
            ->getJson('/api/v1/locations/cities?regionId=' . $region->id . '&search=Уф')
            ->assertOk()
            ->assertJsonPath('data.cities.0.id', $city->id)
            ->assertJsonPath('data.cities.0.name', 'Уфа');
    }

    private function createRegion(string $kladrId, string $name): EloquentRegion
    {
        return EloquentRegion::query()->create([
            'kladr_id'   => $kladrId,
            'name'       => $name,
            'slug'       => str($name)->slug()->toString(),
            'label'      => $name,
            'type'       => 'region',
            'type_short' => 'рег',
            'sort_order' => 1,
        ]);
    }

    private function createCity(EloquentRegion $region, string $kladrId, string $name): EloquentCity
    {
        return EloquentCity::query()->create([
            'region_id'  => $region->id,
            'kladr_id'   => $kladrId,
            'name'       => $name,
            'slug'       => str($name)->slug()->toString(),
            'label'      => $name,
            'type'       => 'city',
            'type_short' => 'г',
            'sort_order' => 1,
        ]);
    }
}
