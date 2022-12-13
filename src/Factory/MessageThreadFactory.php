<?php

namespace App\Factory;

use App\Entity\Entreprise;
use App\Entity\MessageThread;
use App\Entity\Signalement;

class MessageThreadFactory
{
    public function createInstanceFrom(Signalement $signalement, Entreprise $entreprise)
    {
        return (new MessageThread())
            ->setSignalement($signalement)
            ->setEntreprise($entreprise);
    }
}
