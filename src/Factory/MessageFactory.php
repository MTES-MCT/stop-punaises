<?php

namespace App\Factory;

use App\Entity\Message;
use App\Repository\EntrepriseRepository;
use App\Repository\SignalementRepository;

class MessageFactory
{
    public function __construct(
        private SignalementRepository $signalementRepository,
        private EntrepriseRepository $entrepriseRepository,
    ) {
    }

    public function createInstanceFrom(array $data): Message
    {
        $signalement = $this->signalementRepository->findOneBy(['uuid' => $data['signalement_uuid']]);
        $entrprise = $this->entrepriseRepository->findOneBy(['uuid' => $data['entreprise_uuid']]);

        return (new Message())
            ->setSender($entrprise->getUser()->getEmail())
            ->setRecipient($signalement->getEmailOccupant())
            ->setContent($data['message'])
            ->setEntreprise($entrprise)
            ->setSignalement($signalement);
    }
}