<?php

namespace App\Event;

use App\Entity\Entreprise;
use Symfony\Contracts\EventDispatcher\Event;

class EntrepriseRegisteredEvent extends Event
{
    public const NAME = 'entreprise.registered';

    public function __construct(private Entreprise $entreprise)
    {
    }

    public function getEntreprise(): Entreprise
    {
        return $this->entreprise;
    }
}
