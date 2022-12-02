<?php

namespace App\Service\Signalement;

use App\Entity\Entreprise;
use App\Entity\Signalement;

class EventsProvider
{
    public function __construct(
        private Signalement $signalement,
        private string $pdfUrl,
        private bool $isAdmin = false,
        private ?Entreprise $entreprise = null,
        ) {
    }

    public function getEvents(): array
    {
        $events = [];

        if ($this->signalement->getClosedAt()) {
            $events[] = [
                'date' => $this->signalement->getClosedAt(),
                'title' => 'Signalement terminé',
                'description' => 'Vous avez mis fin à la procédure. Merci d\'avoir utilisé Stop Punaises.',
            ];
        }

        if ($this->signalement->getResolvedAt()) {
            $events[] = [
                'date' => $this->signalement->getResolvedAt(),
                'title' => 'Problème résolu',
                'description' => 'Vous avez résolu votre problème ! Merci d\'avoir utilisé Stop Punaises.',
            ];
        }

        if ($this->signalement->isAutotraitement()) {
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
        } else {
            if ($this->isAdmin || $this->entreprise) {
                $interventions = $this->signalement->getInterventions();
                foreach ($interventions as $intervention) {
                    $action = $intervention->isAccepted() ? 'accepté' : 'refusé';
                    $event = [];
                    if ($this->isAdmin) {
                        $event = [
                            'date' => $intervention->getChoiceByEntrepriseAt(),
                            'title' => $intervention->getEntreprise()->getNom().' a '.$action.' le signalement',
                            'description' => 'L\'entreprise a '.$action.' le signalement',
                        ];
                    } elseif ($intervention->getEntreprise()->getId() == $this->entreprise->getId()) {
                        $event = [
                            'date' => $intervention->getChoiceByEntrepriseAt(),
                            'title' => 'Signalement '.$action,
                            'description' => 'Vous avez '.$action.' le signalement',
                        ];
                    }
                    if (!empty($event)) {
                        if (!$intervention->isAccepted()) {
                            $event['description'] .= ' pour le motif suivant : '.html_entity_decode($intervention->getCommentaireRefus());
                        }
                        $events[] = $event;
                    }
                }
            }

            if ($this->signalement->getSwitchedTraitementAt()) {
                $events[] = [
                    'date' => $this->signalement->getSwitchedTraitementAt(),
                    'title' => 'Signalement transféré',
                    'description' => 'Votre signalement a bien été transmis aux entreprises labellisées. Elles vous contacteront au plus vite.',
                ];
            }
        }

        $events[] = [
            'date' => $this->signalement->getCreatedAt(),
            'title' => 'Signalement déposé',
            'description' => 'Votre signalement a bien été enregistré sur Stop Punaises.',
        ];

        return $events;
    }
}
