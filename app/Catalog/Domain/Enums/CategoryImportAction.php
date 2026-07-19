<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Enums;

enum CategoryImportAction: string
{
    case CREATE     = 'create';
    case UPDATE     = 'update';
    case DEACTIVATE = 'deactivate';
}
