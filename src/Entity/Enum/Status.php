<?php

namespace App\Entity\Enum;

enum Status: string
{
    case INACTIVE = 'inactive';
    case ACTIVE = 'active';
    case ARCHIVE = 'archive';
}
