<?php

declare(strict_types=1);

namespace App\Location\Domain\Enums;

enum LocationImportStatus: string
{
    case PREPARING = 'preparing';
    case PREVIEW   = 'preview';
    case PREVIEWED = 'previewed';
    case APPLYING  = 'applying';
    case APPLIED   = 'applied';
    case FAILED    = 'failed';
}
