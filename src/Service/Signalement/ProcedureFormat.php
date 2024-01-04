<?php

namespace App\Service\Signalement;

use App\Entity\Signalement;

class ProcedureFormat
{
    private string $percent;
    private string $label;

    public function __construct(
        private Signalement $signalement
    ) {
        $this->init();
    }

    public function getPercent(): string
    {
        return $this->percent;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    private function init(): void
    {
        if ($this->signalement->isAutotraitement()) {
            $this->percent = 33;
            $this->label = 'Protocole envoyé';
            if ($this->signalement->getResolvedAt() || $this->signalement->getClosedAt()) {
                $this->percent = 100;
                $this->label = 'Confirmation usager';
            } elseif ($this->signalement->getReminderAutotraitementAt()) {
                $this->percent = 66;
                $this->label = 'Feedback envoyé';
            }
        } else {
            $this->percent = 5;
            $this->label = 'Réception';
            if (!empty($this->signalement->getInterventions())) {
                $this->percent = 20;
                $this->label = 'Contact usager';

                if (!empty($this->signalement->getTypeIntervention())) {
                    $this->percent = 80;
                    $this->label = 'Intervention faite';
                } elseif ($this->signalement->getResolvedAt()) {
                    $this->percent = 100;
                    $this->label = 'Confirmation usager';
                } else {
                    $isPendingEstimation = false;
                    $isSentWithoutAnswer = false;
                    $isAccepted = false;
                    $isRefused = false;
                    $isCanceled = false;

                    foreach ($this->signalement->getInterventions() as $intervention) {
                        if ($intervention->getEstimationSentAt()) {
                            if ($intervention->getCanceledByEntrepriseAt()) {
                                $isCanceled = true;
                            } elseif (false === $intervention->isAcceptedByUsager()) {
                                $isRefused = true;
                            } elseif (true === $intervention->isAcceptedByUsager()) {
                                $isAccepted = true;
                            } else {
                                $isSentWithoutAnswer = true;
                            }
                        } else {
                            $isPendingEstimation = true;
                        }
                    }

                    if ($isAccepted) {
                        $this->percent = 60;
                        $this->label = 'Estimation acceptée';
                    } elseif ($isSentWithoutAnswer) {
                        $this->percent = 40;
                        $this->label = 'Estimation envoyée';
                    } elseif ($isPendingEstimation) {
                        $this->percent = 20;
                        $this->label = 'Contact usager';
                    } elseif ($isRefused) {
                        $this->percent = 100;
                        $this->label = 'Estimation refusée';
                    } elseif ($isCanceled) {
                        $this->percent = 5;
                        $this->label = 'Intervention annulée';
                    }
                }
            }
        }
    }
}
