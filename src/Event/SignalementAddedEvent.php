<?php

namespace App\Event;

use App\Entity\Signalement;
use Symfony\Contracts\EventDispatcher\Event;

class SignalementAddedEvent extends Event
{
    public const NAME = 'signalement.added';

    public function __construct(
        private Signalement $signalement,
        private string $pdfUrl,
        private int $pdfSize,
        private ?\DateTimeImmutable $createdAt = null,
    ) {
    }

    public function getSignalement(): Signalement
    {
        return $this->signalement;
    }

    public function getPdfUrl(): string
    {
        return $this->pdfUrl;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getPdfSize(): int
    {
        return $this->pdfSize;
    }
}
