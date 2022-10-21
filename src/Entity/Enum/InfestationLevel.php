<?php

namespace App\Entity\Enum;

enum InfestationLevel: int
{
    case NULLE = 0;
    case FAIBLE = 1;
    case MOYENNE = 2;
    case ELEVEE = 3;
    case TRES_ELEVEE = 4;

    public function label(): string
    {
        return match ($this) {
            InfestationLevel::NULLE => '0 - Nulle',
            InfestationLevel::FAIBLE => '1 - Faible',
            InfestationLevel::MOYENNE => '2 - Moyenne',
            InfestationLevel::ELEVEE => '3 - Elevée',
            InfestationLevel::TRES_ELEVEE => '4 - Très élevée',
        };
    }
}
