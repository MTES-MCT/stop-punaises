<?php

namespace App\Service\Signalement;

class CartoStatutService
{
    public function __construct()
    {
    }

    public function calculateStatut(array $signalements, \DateTimeImmutable $date): array
    {
        $signalementsStatues = [];
        foreach ($signalements as $signalement) {
            $date4months = $date->modify('-4 month');
            if (null !== $signalement['resolvedAt'] && $signalement['resolvedAt'] < $date) {
                $statut = 'resolved';
            } elseif (null !== $signalement['closedAt'] && $signalement['closedAt'] < $date4months) {
                $statut = 'resolved';
            } elseif (false === $signalement['active'] && $signalement['createdAt'] < $date4months) {
                $statut = 'resolved';
            } elseif (null !== $signalement['closedAt']
                    && $signalement['closedAt'] > $date4months && $signalement['closedAt'] < $date) {
                $statut = 'trace';
            } elseif (false === $signalement['active']
                    && $signalement['createdAt'] > $date4months && $signalement['createdAt'] < $date) {
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
