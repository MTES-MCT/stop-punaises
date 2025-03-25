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
        $date4monthsAgo = $date->modify('-4 month');
        foreach ($signalements as $signalement) {
            $dateResolvedAt = $signalement['resolved_at'] ? new \DateTimeImmutable($signalement['resolved_at']) : null;
            $dateClosedAt = $signalement['closed_at'] ? new \DateTimeImmutable($signalement['closed_at']) : null;
            $dateCreatedAt = new \DateTimeImmutable($signalement['created_at']);
            if (null !== $dateResolvedAt && $dateResolvedAt < $date) {
                $statut = 'resolved';
                continue;
            } elseif (null !== $dateClosedAt && $dateClosedAt < $date4monthsAgo) {
                $statut = 'resolved';
                continue;
            } elseif ($dateCreatedAt < $date4monthsAgo) {
                $statut = 'resolved';
                continue;
            } elseif (null !== $dateClosedAt
                    && $dateClosedAt > $date4monthsAgo && $dateClosedAt < $date) {
                $statut = 'trace';
            } elseif ($dateCreatedAt > $date4monthsAgo && $dateCreatedAt < $date) {
                $statut = 'trace';
            } else {
                $statut = 'en cours';
            }
            $signalement['statut'] = $statut;
            unset($signalement['resolved_at']);
            unset($signalement['closed_at']);
            unset($signalement['created_at']);
            $signalementsStatues[] = $signalement;
        }

        return $signalementsStatues;
    }
}
