<?php

declare(strict_types=1);

namespace Tests\Feature\Shared;

use Tests\Feature\FeatureTestCase;

class PlatformCapabilitiesTest extends FeatureTestCase
{
    public function test_public_capability_contract_exposes_only_implemented_user_features(): void
    {
        $this->getJson('/api/v1/capabilities')
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    'account'        => [
                        'deactivation' => false,
                        'deletion'     => false,
                    ],
                    'notifications'  => [
                        'eventKeys' => [
                            'listing_moderation',
                            'favorite_listings',
                            'security_login',
                        ],
                    ],
                    'sellerProfiles' => [
                        'enabled' => false,
                    ],
                ],
            ]);
    }
}
