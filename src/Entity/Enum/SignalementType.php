<?php

namespace App\Entity\Enum;

enum SignalementType: string
{
    case TYPE_LOGEMENT = 'TYPE_LOGEMENT';
    case TYPE_ERP = 'TYPE_ERP';
    case TYPE_TRANSPORT = 'TYPE_TRANSPORT';
}
