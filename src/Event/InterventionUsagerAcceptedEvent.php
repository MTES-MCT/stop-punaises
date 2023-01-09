<?php

namespace App\Event;

use App\Entity\Intervention;
use Symfony\Contracts\EventDispatcher\Event;

class InterventionUsagerAcceptedEvent extends Event
{
    public const NAME = 'intervention.usager.accepted';

    public function __construct(private Intervention $intervention)
    {
    }

    public function getIntervention(): Intervention
    {
        return $this->intervention;
    }
}
