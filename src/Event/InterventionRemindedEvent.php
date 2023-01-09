<?php

namespace App\Event;

use App\Entity\Intervention;
use Symfony\Contracts\EventDispatcher\Event;

class InterventionRemindedEvent extends Event
{
    public const NAME = 'intervention.reminded';

    public function __construct(private Intervention $intervention)
    {
    }

    public function getIntervention(): Intervention
    {
        return $this->intervention;
    }
}
