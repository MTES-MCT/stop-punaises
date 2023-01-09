<?php

namespace App\Event;

use App\Entity\Signalement;
use Symfony\Contracts\EventDispatcher\Event;

class SignalementSwitchedEvent extends Event
{
    public const NAME_AUTOTRAITEMENT = 'signalement.switched.autotraitement';
    public const NAME_PRO = 'signalement.switched.pro';

    public function __construct(private Signalement $signalement)
    {
    }

    public function getSignalement(): Signalement
    {
        return $this->signalement;
    }
}
