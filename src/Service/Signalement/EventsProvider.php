<?php

namespace App\Service\Signalement;

use App\Entity\Signalement;

class EventsProvider
{
    public function __construct(private Signalement $signalement, private string $pdfUrl)
    {
    }

    public function getEvents(): array
    {
        $events = [];

        if ($this->signalement->getResolvedAt()) {
            $events[] = [
                'date' => $this->signalement->getResolvedAt(),
                'title' => 'Problème résolu',
                'description' => 'Vous avez résolu votre problème ! Merci d\'avoir utilisé Stop Punaises.',
            ];
        }

        if ($this->signalement->getReminderAutotraitementAt()) {
            $event = [
                'date' => $this->signalement->getReminderAutotraitementAt(),
                'title' => 'Suivi du traitement',
                'description' => 'Votre problème de punaises est-il résolu ?',
            ];
            if (!$this->signalement->getResolvedAt()) {
                $event['label'] = 'Nouveau';
                $event['actionLabel'] = 'En savoir plus';
                $event['modalToOpen'] = 'probleme-resolu';
            }
            $events[] = $event;
        }

        $events[] = [
            'date' => $this->signalement->getCreatedAt(),
            'title' => 'Protocole envoyé',
            'description' => 'Le protocole d\'auto traitement a bien été envoyé.',
            'actionLabel' => 'Télécharger le protocole',
            'actionLink' => $this->pdfUrl,
        ];

        $events[] = [
            'date' => $this->signalement->getCreatedAt(),
            'title' => 'Signalement déposé',
            'description' => 'Votre signalement a bien été enregistré sur Stop Punaises.',
        ];

        return $events;
    }
}
