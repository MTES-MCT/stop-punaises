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
            $dateResolvedAt = new \DateTimeImmutable($signalement['resolved_at']);
            $dateClosedAt = new \DateTimeImmutable($signalement['closed_at']);
            $dateCreatedAt = new \DateTimeImmutable($signalement['created_at']);
            if (null !== $dateResolvedAt && $dateResolvedAt < $date) {
                $statut = 'resolved';
            } elseif (null !== $dateClosedAt && $dateClosedAt < $date4monthsAgo) {
                $statut = 'resolved';
            } elseif ($dateCreatedAt < $date4monthsAgo) {
                $statut = 'resolved';
            } elseif (null !== $dateClosedAt
                    && $dateClosedAt > $date4monthsAgo && $dateClosedAt < $date) {
                $statut = 'trace';
            } elseif ($dateCreatedAt > $date4monthsAgo && $dateCreatedAt < $date) {
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
