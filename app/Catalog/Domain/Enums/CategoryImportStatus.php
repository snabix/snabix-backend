<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Enums;

enum CategoryImportStatus: string
{
    case PREVIEW     = 'preview';
    case APPLIED     = 'applied';
    case ROLLED_BACK = 'rolled_back';
}
