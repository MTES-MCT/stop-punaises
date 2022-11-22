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
            InfestationLevel::NULLE => self::getLabelList()[0],
            InfestationLevel::FAIBLE => self::getLabelList()[1],
            InfestationLevel::MOYENNE => self::getLabelList()[2],
            InfestationLevel::ELEVEE => self::getLabelList()[3],
            InfestationLevel::TRES_ELEVEE => self::getLabelList()[4],
        };
    }

    public static function getLabelList(): array
    {
        return [
            '0 - Nulle',
            '1 - Faible',
            '2 - Moyenne',
            '3 - Elevée',
            '4 - Très élevée',
        ];
    }
}
