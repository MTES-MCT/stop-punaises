<?php

namespace App\Entity\Enum;

enum PlaceType: string
{
    case TYPE_ERP_PUBLIC = 'TYPE_ERP_PUBLIC';
    case TYPE_ERP_PRIVE = 'TYPE_ERP_PRIVE';
    case TYPE_ERP_NSP = 'TYPE_ERP_NSP';
    case TYPE_TRANSPORT_BUS = 'TYPE_TRANSPORT_BUS';
    case TYPE_TRANSPORT_METRO = 'TYPE_TRANSPORT_METRO';
    case TYPE_TRANSPORT_TRAM = 'TYPE_TRANSPORT_TRAM';
    case TYPE_TRANSPORT_TRAIN = 'TYPE_TRANSPORT_TRAIN';
    case TYPE_TRANSPORT_AUTRE = 'TYPE_TRANSPORT_AUTRE';

    public function label(): string
    {
        return self::getLabelList()[$this->name];
    }

    public static function getLabelList(): array
    {
        return [
            'TYPE_ERP_PUBLIC' => 'Public',
            'TYPE_ERP_PRIVE' => 'Privé',
            'TYPE_ERP_NSP' => 'Ne sait pas',
            'TYPE_TRANSPORT_BUS' => 'Bus',
            'TYPE_TRANSPORT_METRO' => 'Metro',
            'TYPE_TRANSPORT_TRAM' => 'Tram',
            'TYPE_TRANSPORT_TRAIN' => 'Train',
            'TYPE_TRANSPORT_AUTRE' => 'Autre',
        ];
    }

    public static function getLabelListERP(): array
    {
        return [
            'TYPE_ERP_PUBLIC' => 'Public',
            'TYPE_ERP_PRIVE' => 'Privé',
            'TYPE_ERP_NSP' => 'Ne sait pas',
        ];
    }

    public static function getLabelListTransport(): array
    {
        return [
            'TYPE_TRANSPORT_BUS' => 'Bus',
            'TYPE_TRANSPORT_METRO' => 'Metro',
            'TYPE_TRANSPORT_TRAM' => 'Tram',
            'TYPE_TRANSPORT_TRAIN' => 'Train',
            'TYPE_TRANSPORT_AUTRE' => 'Autre',
        ];
    }

    public static function fromLabel(string $label): self
    {
        $key = array_search($label, self::getLabelList());

        return self::from($key);
    }

    public static function tryFromLabel(string $label): ?self
    {
        $key = array_search($label, self::getLabelList());

        return self::tryFrom($key);
    }
}
