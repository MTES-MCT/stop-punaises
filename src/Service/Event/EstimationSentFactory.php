<?php

namespace App\Service\Event;

use App\Entity\Event;
use App\Entity\Intervention;
use App\Manager\EventManager;
use App\Repository\EventRepository;

class EstimationSentFactory
{
    public function __construct(
        private EventManager $eventManager,
        private EventRepository $eventRepository,
    ) {
    }

    public function add(Intervention $intervention, ?\DateTimeImmutable $createdAt, int $entrepriseUserId): void
    {
        $signalement = $intervention->getSignalement();
        $event = $this->eventManager->createEventEstimationSent(
            signalement: $signalement,
            title: 'Estimation '.$intervention->getEntreprise()->getNom(),
            description: 'L\'entreprise '.$intervention->getEntreprise()->getNom().' a envoyé une estimation',
            recipient: null,
            userId: Event::USER_ADMIN,
            userIdExcluded: null,
            label: 'Nouveau',
            actionLabel: 'En savoir plus',
            actionLink: 'modalToOpen:view-estimation-'.$intervention->getId(),
        );
        $this->eventRepository->updateCreatedAt($event, $createdAt);

        $event = $this->eventManager->createEventEstimationSent(
            signalement: $signalement,
            title: 'Estimation '.$intervention->getEntreprise()->getNom(),
            description: 'Vous avez envoyé une estimation à l\'usager.',
            recipient: null,
            userId: $entrepriseUserId,
            userIdExcluded: null,
            label: 'Nouveau',
            actionLabel: 'En savoir plus',
            actionLink: 'modalToOpen:view-estimation-'.$intervention->getId(),
        );
        $this->eventRepository->updateCreatedAt($event, $createdAt);

        $event = $this->eventManager->createEventEstimationSent(
            signalement: $signalement,
            title: 'Estimation '.$intervention->getEntreprise()->getNom(),
            description: 'L\'entreprise '.$intervention->getEntreprise()->getNom().' vous a envoyé une estimation',
            recipient: $intervention->getSignalement()->getEmailOccupant(),
            userId: null,
            userIdExcluded: null,
            label: 'Nouveau',
            actionLabel: 'En savoir plus',
            actionLink: 'modalToOpen:choice-estimation-'.$intervention->getId(),
        );
        $this->eventRepository->updateCreatedAt($event, $createdAt);
    }
}
