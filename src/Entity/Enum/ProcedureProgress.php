<?php

namespace App\Entity\Enum;

enum ProcedureProgress: string
{
    case AUTO_PROTOCOLE_ENVOYE = 'AUTO_PROTOCOLE_ENVOYE';
    case AUTO_FEEDBACK_ENVOYE = 'AUTO_FEEDBACK_ENVOYE';
    case AUTO_CONFIRMATION_USAGER = 'AUTO_CONFIRMATION_USAGER';
    case PRO_RECEPTION = 'PRO_RECEPTION';
    case PRO_INTERVENTION_ANNULEE = 'PRO_INTERVENTION_ANNULEE';
    case PRO_CONTACT_USAGER = 'PRO_CONTACT_USAGER';
    case PRO_ESTIMATION_ENVOYEE = 'PRO_ESTIMATION_ENVOYEE';
    case PRO_ESTIMATION_ACCEPTEE = 'PRO_ESTIMATION_ACCEPTEE';
    case PRO_INTERVENTION_FAITE = 'PRO_INTERVENTION_FAITE';
    case PRO_ESTIMATION_REFUSEE = 'PRO_ESTIMATION_REFUSEE';

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

    private static function getLabelList(): array
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

    public function percent(): int
    {
        return match ($this) {
            self::AUTO_PROTOCOLE_ENVOYE => self::getPercentList()[0],
            self::AUTO_FEEDBACK_ENVOYE => self::getPercentList()[1],
            self::AUTO_CONFIRMATION_USAGER => self::getPercentList()[2],
            self::PRO_RECEPTION => self::getPercentList()[3],
            self::PRO_INTERVENTION_ANNULEE => self::getPercentList()[4],
            self::PRO_CONTACT_USAGER => self::getPercentList()[5],
            self::PRO_ESTIMATION_ENVOYEE => self::getPercentList()[6],
            self::PRO_ESTIMATION_ACCEPTEE => self::getPercentList()[7],
            self::PRO_INTERVENTION_FAITE => self::getPercentList()[8],
            self::PRO_ESTIMATION_REFUSEE => self::getPercentList()[9],
        };
    }

    private static function getPercentList(): array
    {
        return [
            33,
            66,
            100,
            5,
            5,
            20,
            40,
            60,
            80,
            100,
        ];
    }
}
