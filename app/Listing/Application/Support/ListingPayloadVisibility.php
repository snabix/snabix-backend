<?php

declare(strict_types=1);

namespace App\Listing\Application\Support;

enum ListingPayloadVisibility
{
    case PRIVATE_VIEW;
    case PUBLIC_VIEW;
}
