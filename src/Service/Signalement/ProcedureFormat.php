<?php

namespace App\Service\Signalement;

class ProcedureFormat
{
    private string $percent;
    private string $label;

    public function __construct(
        private string $procedureLabel
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
        $this->label = $this->procedureLabel;
        switch ($this->procedureLabel) {
            case 'Protocole envoyé':
                $this->percent = 33;
                break;
            case 'Confirmation usager':
                $this->percent = 100;
                break;
            case 'Feedback envoyé':
                $this->percent = 66;
                break;

            case 'Réception':
                $this->percent = 5;
                break;
            case 'Contact usager':
                $this->percent = 20;
                break;
            case 'Intervention faite':
                $this->percent = 80;
                break;
            case 'Estimation acceptée':
                $this->percent = 60;
                break;
            case 'Estimation envoyée':
                $this->percent = 40;
                break;
            case 'Estimation refusée':
                $this->percent = 100;
                break;
            case 'Intervention annulée':
                $this->percent = 5;
                break;
            default:
                $this->percent = 0;
                break;
        }
    }
}
