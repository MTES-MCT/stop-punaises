<?php

namespace App\Event;

use App\Entity\Signalement;
use Symfony\Contracts\EventDispatcher\Event;

class SignalementClosedEvent extends Event
{
    public const NAME = 'signalement.closed';

    public function __construct(
        private Signalement $signalement,
        private ?\DateTimeImmutable $createdAt = null,
        ) {
    }

    public function getSignalement(): Signalement
    {
        return $this->signalement;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
