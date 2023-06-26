<?php

namespace App\Entity\Behaviour;

use Doctrine\ORM\Mapping as ORM;

trait TimestampableTrait
{
    private ?\DateTimeImmutable $customCreatedAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist()]
    public function setCreatedAtValue(): self
    {
        if ($this->hasCustomCreatedAt()) {
            $this->createdAt = $this->getCustomCreatedAt();
        } else {
            $this->createdAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[ORM\PreUpdate()]
    public function setUpdatedAtValue(): self
    {
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function hasCustomCreatedAt(): bool
    {
        return null !== $this->customCreatedAt;
    }

    public function getCustomCreatedAt(): \DateTimeImmutable
    {
        return $this->customCreatedAt;
    }

    public function setCustomCreatedAt(\DateTimeImmutable $customCreatedAt): self
    {
        $this->customCreatedAt = $customCreatedAt;

        return $this;
    }
}
