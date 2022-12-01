<?php

namespace App\Service\Signalement;

use App\Entity\Signalement;
use DateTime;

class EventsProvider
{
    public function __construct(private Signalement $signalement, private string $pdfUrl)
    {
    }

    public function getEvents(): array
    {
        $buffer = [];

        if ($this->signalement->isAutotraitement()) {
            $todayDate = new DateTime();
            if ($todayDate->diff($this->signalement->getCreatedAt())->format('%a') >= 45) {
                $buffer[] = [
                    'date' => $todayDate,
                    'label' => 'Nouveau',
                    'title' => 'Suivi du traitement',
                    'description' => 'Votre problème de punaises est-il résolu ?',
                    'actionLabel' => 'En savoir plus',
                    'actionLink' => '#',
                ];
            }

            $buffer[] = [
                'date' => $this->signalement->getCreatedAt(),
                'title' => 'Protocole envoyé',
                'description' => 'Le protocole d\'auto traitement a bien été envoyé.',
                'actionLabel' => 'Télécharger le protocole',
                'actionLink' => $this->pdfUrl,
            ];
        } else {
            $todayDate = new DateTime();
            // TODO : comparer avec closedDate + 15j
            if (false && $todayDate->diff($this->signalement->getCreatedAt())->format('%a') >= 15) {
                $buffer[] = [
                    'date' => $todayDate,
                    'label' => 'Nouveau',
                    'title' => 'Suivi du traitement',
                    'description' => 'Votre problème de punaises est-il résolu ?',
                    'actionLabel' => 'En savoir plus',
                    'actionLink' => '#',
                ];
            }
        }

        $buffer[] = [
            'date' => $this->signalement->getCreatedAt(),
            'title' => 'Signalement déposé',
            'description' => 'Votre signalement a bien été enregistré sur Stop Punaises.',
        ];

        return $buffer;
    }
}
