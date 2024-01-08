<?php

namespace App\Service\Signalement;

class ProcedureFormat
{
    public static function getPercentByLabel(string $label): int
    {
        switch ($label) {
            case 'Protocole envoyé':
                return 33;
                break;
            case 'Confirmation usager':
                return 100;
                break;
            case 'Feedback envoyé':
                return 66;
                break;

            case 'Réception':
                return 5;
                break;
            case 'Contact usager':
                return 20;
                break;
            case 'Intervention faite':
                return 80;
                break;
            case 'Estimation acceptée':
                return 60;
                break;
            case 'Estimation envoyée':
                return 40;
                break;
            case 'Estimation refusée':
                return 100;
                break;
            case 'Intervention annulée':
                return 5;
                break;
            default:
                return 0;
                break;
        }
    }
}
