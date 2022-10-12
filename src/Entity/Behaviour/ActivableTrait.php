<?php

namespace App\Entity\Behaviour;

use Doctrine\ORM\Mapping as ORM;

trait ActivableTrait
{
    #[ORM\Column]
    private ?bool $active = true;

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }
}
