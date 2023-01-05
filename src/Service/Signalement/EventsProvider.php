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

        $this->addEventSuiviTraitement();

        $this->addEventEmptyEstimationList();
        $this->addEventsIntervention();

        usort($this->events, fn ($a, $b) => $a['date'] > $b['date'] ? -1 : 1);

        return $this->events;
    }

    private function addEventSuiviTraitement()
    {
        if (!$this->signalement->isAutotraitement()) {
            $interventions = $this->signalement->getInterventions();
            foreach ($interventions as $intervention) {
                if ($intervention->getReminderResolvedByEntrepriseAt()) {
                    $event = [
                        'date' => $intervention->getReminderResolvedByEntrepriseAt(),
                        'title' => 'Suivi du traitement',
                        'description' => 'Votre problème de punaises est-il résolu ?',
                    ];
                    if ($this->isBackOffice) {
                        $event['description'] = 'L\'email de suivi post-traitement a été envoyé à l\'usager';
                    }
                    if (!$this->signalement->getResolvedAt()) {
                        $event['label'] = 'Nouveau';
                        if (!$this->isBackOffice) {
                            $event['actionLabel'] = 'En savoir plus';
                            $event['modalToOpen'] = 'probleme-resolu-pro';
                        }
                    }
                    $this->events[] = $event;
                }
            }
        }
    }

    private function addEventEmptyEstimationList()
    {
        if (!$this->signalement->isAutotraitement() && ($this->isAdmin || !$this->entreprise)) {
            $isEventDisplayed = false;
            $isEstimationReceived = false;
            $mostRecentDate = 0;
            $interventions = $this->signalement->getInterventions();
            if (\count($interventions) > 0) {
                $isEventDisplayed = true;
                foreach ($interventions as $intervention) {
                    if ($intervention->getEstimationSentAt()) {
                        $isEstimationReceived = true;
                    }
                    if ($intervention->isAccepted() && !$intervention->getChoiceByUsagerAt()) {
                        $isEventDisplayed = false;
                        break;
                    } elseif ($intervention->getChoiceByUsagerAt()) {
                        if ($intervention->isAcceptedByUsager()) {
                            $isEventDisplayed = false;
                            break;
                        }
                        if (!$mostRecentDate || $intervention->getChoiceByUsagerAt() > $mostRecentDate) {
                            $mostRecentDate = $intervention->getChoiceByUsagerAt();
                        }
                    }
                }
            }

            if ($isEventDisplayed) {
                $event = [];
                $event['date'] = $mostRecentDate;
                $event['title'] = $isEstimationReceived ? 'Plus d\'estimation disponible' : 'Aucune entreprise disponible';
                if ($this->isAdmin) {
                    $event['description'] = $isEstimationReceived ? 'L\'usager a refusé toutes les estimations des entreprises' : 'Aucune entreprise n\'est en capacité de traiter cette demande';
                } else {
                    $event['description'] = $isEstimationReceived ? 'Vous avez refusé toutes les estimations des entreprises' : 'Aucune entreprise n\'est en capacité de traiter votre demande';

                    if (!$this->signalement->getClosedAt() && !$this->signalement->getResolvedAt()) {
                        $event['actionLabel'] = 'En savoir plus';
                        $event['modalToOpen'] = 'empty-estimations';
                    }
                }
                $this->events[] = $event;
            }
        }
    }

    private function addEventsIntervention()
    {
        if (!$this->signalement->isAutotraitement()) {
            $interventions = $this->signalement->getInterventions();
            foreach ($interventions as $intervention) {
                // Traité
                if ($intervention->getResolvedByEntrepriseAt()) {
                    $event = [];
                    $event['date'] = $intervention->getEstimationSentAt();
                    $event['title'] = 'Intervention faite';
                    $event['description'] = '';
                    if ($this->isAdmin) {
                        $event['description'] = 'L\'entreprise '.$intervention->getEntreprise()->getNom().' a marqué le signalement comme traité';
                    } elseif ($this->entreprise && $intervention->getEntreprise()->getId() == $this->entreprise->getId()) {
                        $event['description'] = 'Vous avez marqué le signalement comme traité';
                    } elseif (!$this->entreprise) {
                        $event['title'] = 'Traitement effectué';
                        $event['description'] = 'L\'entreprise '.$intervention->getEntreprise()->getNom().' a indiqué avoir traité votre domicile';
                    }

                    $this->events[] = $event;
                }

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

                if ($this->isAdmin || $this->entreprise) {
                    // Signalement accepté / refusé
                    $action = $intervention->isAccepted() ? 'accepté' : 'refusé';
                    $event = [];
                    $event['date'] = $intervention->getChoiceByEntrepriseAt();
                    if ($this->isAdmin) {
                        $event['title'] = $intervention->getEntreprise()->getNom().' a '.$action.' le signalement';
                        $event['description'] = 'L\'entreprise a '.$action.' le signalement';
                    } elseif ($intervention->getEntreprise()->getId() == $this->entreprise->getId()) {
                        $event['title'] = 'Signalement '.$action;
                        $event['description'] = 'Vous avez '.$action.' le signalement';
                    }
                    if (!empty($event['title'])) {
                        if (!$intervention->isAccepted()) {
                            $event['description'] .= ' pour le motif suivant : '.html_entity_decode($intervention->getCommentaireRefus());
                        }
                        $this->events[] = $event;
                    }
                }
            }
        }
    }
}
