<?php

namespace App\Entity\Enum;

enum ProcedureProgress: int
{
    case AUTO_PROTOCOLE_ENVOYE = 33;
    case AUTO_FEEDBACK_ENVOYE = 66;
    case AUTO_CONFIRMATION_USAGER = 100;
    case PRO_RECEPTION = 5;
    case PRO_INTERVENTION_ANNULEE = 6;
    case PRO_CONTACT_USAGER = 20;
    case PRO_ESTIMATION_ENVOYEE = 40;
    case PRO_ESTIMATION_ACCEPTEE = 60;
    case PRO_INTERVENTION_FAITE = 80;
    case PRO_ESTIMATION_REFUSEE = 99;

    public function label(): string
    {
        return match ($this) {
            self::AUTO_PROTOCOLE_ENVOYE => self::getLabelList()[0],
            self::AUTO_FEEDBACK_ENVOYE => self::getLabelList()[1],
            self::AUTO_CONFIRMATION_USAGER => self::getLabelList()[2],
            self::PRO_RECEPTION => self::getLabelList()[3],
            self::PRO_INTERVENTION_ANNULEE => self::getLabelList()[4],
            self::PRO_CONTACT_USAGER => self::getLabelList()[5],
            self::PRO_ESTIMATION_ENVOYEE => self::getLabelList()[6],
            self::PRO_ESTIMATION_ACCEPTEE => self::getLabelList()[7],
            self::PRO_INTERVENTION_FAITE => self::getLabelList()[8],
            self::PRO_ESTIMATION_REFUSEE => self::getLabelList()[9],
        };
    }

    public static function getLabelList(): array
    {
        return [
            'Protocole envoyé',
            'Feedback envoyé',
            'Confirmation usager',
            'Réception',
            'Intervention annulée',
            'Contact usager',
            'Estimation envoyée',
            'Estimation acceptée',
            'Intervention faite',
            'Estimation refusée',
        ];
    }
}
