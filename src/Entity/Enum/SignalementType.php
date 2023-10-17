<?php

namespace App\Entity\Enum;

enum SignalementType: string
{
    case TYPE_LOGEMENT = 'TYPE_LOGEMENT';
    case TYPE_ERP = 'TYPE_ERP';
    case TYPE_TRANSPORT = 'TYPE_TRANSPORT';

    public function label(): string
    {
        return self::getLabelList()[$this->name];
    }

    public static function getLabelList(): array
    {
        return [
            'TYPE_LOGEMENT' => 'Logement',
            'TYPE_ERP' => 'ERP',
            'TYPE_TRANSPORT' => 'Transport',
        ];
    }
}
