<?php

namespace App\Service\Signalement;

use App\Entity\Entreprise;
use App\Entity\Signalement;

class EventsProvider
{
    private array $events;

    public function __construct(
        private Signalement $signalement,
        private string $pdfUrl,
        private bool $isAdmin = false,
        private bool $isBackOffice = false,
        private ?Entreprise $entreprise = null,
        ) {
    }

    public function getEvents(): array
    {
        $this->events = [];

        $this->addEventsIntervention();

        return $this->events;
    }

    private function addEventsIntervention()
    {
        if (!$this->signalement->isAutotraitement()) {
            $interventions = $this->signalement->getInterventions();
            foreach ($interventions as $intervention) {
                // Estimation envoyée
                if ($intervention->getEstimationSentAt()) {
                    $event = [];
                    $event['date'] = $intervention->getEstimationSentAt();
                    $event['actionLabel'] = 'En savoir plus';
                    if ($this->isAdmin) {
                        $event['title'] = 'Estimation '.$intervention->getEntreprise()->getNom();
                        $event['description'] = 'L\'entreprise '.$intervention->getEntreprise()->getNom().' a envoyé une estimation';
                        $event['modalToOpen'] = 'view-estimation-'.$intervention->getId();
                    } elseif ($this->entreprise && $intervention->getEntreprise()->getId() == $this->entreprise->getId()) {
                        $event['title'] = 'Estimation '.$this->entreprise->getNom();
                        $event['description'] = 'Vous avez envoyé une estimation à l\'usager.';
                        $event['modalToOpen'] = 'view-estimation-'.$intervention->getId();
                    } elseif (!$this->entreprise) {
                        $event['title'] = 'Estimation '.$intervention->getEntreprise()->getNom();
                        $event['description'] = 'L\'entreprise '.$intervention->getEntreprise()->getNom().' vous a envoyé une estimation';
                        if (!$intervention->getChoiceByUsagerAt()) {
                            $event['modalToOpen'] = 'choice-estimation-'.$intervention->getId();
                        } elseif ($intervention->isAcceptedByUsager()) {
                            $event['modalToOpen'] = 'estimation-accepted-'.$intervention->getId();
                        } else {
                            $event['actionLabel'] = '';
                        }
                    } elseif ($intervention->isAcceptedByUsager()) {
                        $event['title'] = 'Estimation '.$intervention->getEntreprise()->getNom();
                        $event['description'] = 'L\'entreprise '.$intervention->getEntreprise()->getNom().' a envoyé une estimation';
                    }

                    if (!$intervention->getChoiceByUsagerAt()) {
                        $event['label'] = 'Nouveau';
                    } elseif ($intervention->isAcceptedByUsager()) {
                        $event['label'] = 'Estimation acceptée';
                    } else {
                        $event['label'] = 'Estimation refusée';
                    }

                    if (!empty($event['title'])) {
                        $this->events[] = $event;
                    }
                }
            }
        }
    }
}
