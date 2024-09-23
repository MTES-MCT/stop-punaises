<?php

namespace App\Entity\Behaviour;

interface EntityHistoryInterface
{
    public function getId(): ?int;

    public function getHistoryRegisteredEvent(): array;
}
