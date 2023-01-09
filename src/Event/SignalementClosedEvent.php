<?php

namespace App\Event;

use App\Entity\Signalement;
use Symfony\Contracts\EventDispatcher\Event;

class SignalementClosedEvent extends Event
{
    public const NAME = 'signalement.closed';

    public function __construct(private Signalement $signalement)
    {
    }

    public function getSignalement(): Signalement
    {
        return $this->signalement;
    }
}
