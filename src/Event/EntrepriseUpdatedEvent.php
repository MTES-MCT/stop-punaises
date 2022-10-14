<?php

namespace App\Event;

use App\Entity\Entreprise;
use Symfony\Contracts\EventDispatcher\Event;

class EntrepriseUpdatedEvent extends Event
{
    public const NAME = 'entreprise.updated.email';

    public function __construct(private Entreprise $entreprise, private string $currentEmail)
    {
    }

    public function getEntreprise(): Entreprise
    {
        return $this->entreprise;
    }

    public function getCurrentEmail(): string
    {
        return $this->currentEmail;
    }
}
