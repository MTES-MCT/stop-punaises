<?php

namespace App\Service\Signalement;

class ProcedureFormat
{
    private const LABEL_PERCENT = [
        'Protocole envoyé' => 33,
        'Confirmation usager' => 100,
        'Feedback envoyé' => 66,
        'Réception' => 5,
        'Contact usager' => 20,
        'Intervention faite' => 80,
        'Estimation acceptée' => 60,
        'Estimation envoyée' => 40,
        'Estimation refusée' => 100,
        'Intervention annulée' => 5,
    ];

    public static function getPercentByLabel(string $label): int
    {
        if (!empty(self::LABEL_PERCENT[$label])) {
            return self::LABEL_PERCENT[$label];
        }

        return 0;
    }
}
