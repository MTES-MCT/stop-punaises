<?php

namespace App\Service\Signalement;

class CartoStatutCalculator
{
    public function __construct()
    {
    }

    public function calculate(array $signalements, \DateTimeImmutable $date): array
    {
        $signalementsStatues = [];
        foreach ($signalements as $signalement) {
            $date4monthsAgo = $date->modify('-4 month');
            if (null !== $signalement['resolvedAt'] && $signalement['resolvedAt'] < $date) {
                $statut = 'resolved';
            } elseif (null !== $signalement['closedAt'] && $signalement['closedAt'] < $date4monthsAgo) {
                $statut = 'resolved';
            } elseif (false === $signalement['active'] && $signalement['createdAt'] < $date4monthsAgo) {
                $statut = 'resolved';
            } elseif (null !== $signalement['closedAt']
                    && $signalement['closedAt'] > $date4monthsAgo && $signalement['closedAt'] < $date) {
                $statut = 'trace';
            } elseif (false === $signalement['active']
                    && $signalement['createdAt'] > $date4monthsAgo && $signalement['createdAt'] < $date) {
                $statut = 'trace';
            } else {
                $statut = 'en cours';
            }
            $signalement['statut'] = $statut;
            $signalementsStatues[] = $signalement;
        }

        return $signalementsStatues;
    }
}
