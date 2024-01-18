<?php

namespace App\Entity\Enum;

enum SignalementStatus: string
{
    case NEW = 'NEW';
    case CLOSED = 'CLOSED';
    case PROCESSED = 'PROCESSED';
    case CANCELED = 'CANCELED';
    case REFUSED = 'REFUSED';
    case ACTIVE = 'ACTIVE';

    public function label(): string
    {
        return match ($this) {
            self::NEW => self::getLabelList()[0],
            self::CLOSED => self::getLabelList()[1],
            self::PROCESSED => self::getLabelList()[2],
            self::CANCELED => self::getLabelList()[3],
            self::REFUSED => self::getLabelList()[4],
            self::ACTIVE => self::getLabelList()[5],
        };
    }

    private static function getLabelList(): array
    {
        return [
            'Nouveau',
            'Fermé',
            'Traité',
            'Annulé',
            'Refusé',
            'En cours',
        ];
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::NEW => self::getBadgeColorList()[0],
            self::CLOSED => self::getBadgeColorList()[1],
            self::PROCESSED => self::getBadgeColorList()[2],
            self::CANCELED => self::getBadgeColorList()[3],
            self::REFUSED => self::getBadgeColorList()[4],
            self::ACTIVE => self::getBadgeColorList()[5],
        };
    }

    private static function getBadgeColorList(): array
    {
        return [
            'orange-terre-battue',
            'blue-ecume',
            'green-menthe',
            'beige-gris-galet',
            'beige-gris-galet',
            'success',
        ];
    }
}
