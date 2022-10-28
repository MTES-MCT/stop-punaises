<?php

namespace App\Entity\Enum;

enum Declarant: string
{
    case DECLARANT_ENTREPRISE = 'DECLARANT_ENTREPRISE';
    case DECLARANT_OCCUPANT = 'DECLARANT_OCCUPANT';
}
