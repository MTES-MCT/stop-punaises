<?php

namespace App\Dto\Command;

class CommandContext
{
    public function __construct(private ?string $commandName = null)
    {
    }

    public function setCommandName(string $commandName): self
    {
        $this->commandName = $commandName;

        return $this;
    }

    public function getCommandName(): ?string
    {
        return $this->commandName;
    }
}
