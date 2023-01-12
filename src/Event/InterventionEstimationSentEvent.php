<?php

namespace App\Event;

use App\Entity\Intervention;
use Symfony\Contracts\EventDispatcher\Event;

class InterventionEstimationSentEvent extends Event
{
    public const NAME = 'intervention.estimation.sent';

    public function __construct(
        private Intervention $intervention,
        private int $userId,
        private ?\DateTimeImmutable $createdAt = null,
        ) {
    }

    public function getIntervention(): Intervention
    {
        return $this->intervention;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
