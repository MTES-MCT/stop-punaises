<?php

namespace App\Event;

use App\Entity\Intervention;
use Symfony\Contracts\EventDispatcher\Event;

class InterventionUsagerRefusedEvent extends Event
{
    public const NAME = 'intervention.usager.refused';

    public function __construct(private Intervention $intervention)
    {
    }

    public function getIntervention(): Intervention
    {
        return $this->intervention;
    }
}
