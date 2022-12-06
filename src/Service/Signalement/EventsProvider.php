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
        private ?Entreprise $entreprise = null,
        ) {
    }

    public function getEvents(): array
    {
        $this->events = [];

        $this->addEventSignalementTermine();
        $this->addEventSignalementResolu();
        $this->addEventSuiviTraitement();
        $this->addEventProtocoleEnvoye();

        $this->addEventsIntervention();

        $this->addEventSwitchTraitement();
        $this->addEventSignalementDespose();

        return $this->events;
    }

    private function addEventSignalementTermine()
    {
        if ($this->signalement->getClosedAt()) {
            $this->events[] = [
                'date' => $this->signalement->getClosedAt(),
                'title' => 'Signalement terminé',
                'description' => 'Vous avez mis fin à la procédure. Merci d\'avoir utilisé Stop Punaises.',
            ];
        }
    }

    private function addEventSignalementResolu()
    {
        if ($this->signalement->getResolvedAt()) {
            $events[] = [
                'date' => $this->signalement->getResolvedAt(),
                'title' => 'Problème résolu',
                'description' => 'Vous avez résolu votre problème ! Merci d\'avoir utilisé Stop Punaises.',
            ];
        }
    }

    private function addEventSuiviTraitement()
    {
        if ($this->signalement->isAutotraitement() && $this->signalement->getReminderAutotraitementAt()) {
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
            $this->events[] = $event;
        }
    }

    private function addEventProtocoleEnvoye()
    {
        if ($this->signalement->isAutotraitement()) {
            $this->events[] = [
                'date' => $this->signalement->getCreatedAt(),
                'title' => 'Protocole envoyé',
                'description' => 'Le protocole d\'auto traitement a bien été envoyé.',
                'actionLabel' => 'Télécharger le protocole',
                'actionLink' => $this->pdfUrl,
            ];
        }
    }

    private function addEventsIntervention()
    {
        if (!$this->signalement->isAutotraitement()) {
            $interventions = $this->signalement->getInterventions();
            foreach ($interventions as $intervention) {
                // Evénement estimation
                if ($intervention->getEstimationSentAt()) {
                    $event = [];
                    $event['date'] = $intervention->getEstimationSentAt();
                    if ($this->isAdmin) {
                        $event['title'] = 'Estimation '.$intervention->getEntreprise()->getNom();
                        $event['description'] = 'L\'entreprise '.$intervention->getEntreprise()->getNom().' a envoyé une estimation';
                    } elseif ($this->entreprise && $intervention->getEntreprise()->getId() == $this->entreprise->getId()) {
                        $event['title'] = 'Estimation '.$this->entreprise->getNom();
                        $event['description'] = 'Vous avez envoyé une estimation à l\'usager.';
                    } else {
                        $event['title'] = 'Estimation '.$intervention->getEntreprise()->getNom();
                        $event['description'] = 'L\'entreprise '.$intervention->getEntreprise()->getNom().' vous a envoyé une estimation';
                        if (!$intervention->getChoiceByUsagerAt()) {
                            $event['actionLabel'] = 'En savoir plus';
                            $event['modalToOpen'] = 'choice-estimation-'.$intervention->getId();
                        }
                    }

                    if (!$intervention->getChoiceByUsagerAt()) {
                        $event['label'] = 'Nouveau';
                    } elseif ($intervention->isAcceptedByUsager()) {
                        $event['label'] = 'Estimation acceptée';
                    } else {
                        $event['label'] = 'Estimation refusée';
                    }

                    $this->events[] = $event;
                }

                if ($this->isAdmin || $this->entreprise) {
                    // Evénement signalement accepté / refusé
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

    private function addEventSwitchTraitement()
    {
        if (!$this->signalement->isAutotraitement() && $this->signalement->getSwitchedTraitementAt()) {
            $this->events[] = [
                'date' => $this->signalement->getSwitchedTraitementAt(),
                'title' => 'Signalement transféré',
                'description' => 'Votre signalement a bien été transmis aux entreprises labellisées. Elles vous contacteront au plus vite.',
            ];
        }
    }

    private function addEventSignalementDespose()
    {
        $event = [
            'date' => $this->signalement->getCreatedAt(),
            'title' => 'Signalement déposé',
            'description' => 'Votre signalement a bien été enregistré sur Stop Punaises.',
        ];
        if ($this->isAdmin || $this->entreprise) {
            $event['description'] = 'Le signalement a bien été enregistré sur Stop Punaises.';
        }
        $this->events[] = $event;
    }
}
