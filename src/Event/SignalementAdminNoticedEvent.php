<?php

namespace App\Event;

use App\Entity\Signalement;
use Symfony\Contracts\EventDispatcher\Event;

class SignalementAdminNoticedEvent extends Event
{
    public const NAME = 'signalement.admin.noticed';

    public function __construct(private Signalement $signalement)
    {
    }

    public function getSignalement(): Signalement
    {
        return $this->signalement;
    }
}
