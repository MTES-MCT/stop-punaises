<?php

namespace App\EventSubscriber;

use App\Entity\Event;
use App\Event\InterventionUsagerAcceptedEvent;
use App\Manager\EventManager;
use App\Repository\EventRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InterventionUsagerAcceptedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EventManager $eventManager,
        private EventRepository $eventRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InterventionUsagerAcceptedEvent::NAME => 'onInterventionUsagerAccepted',
        ];
    }

    public function onInterventionUsagerAccepted(InterventionUsagerAcceptedEvent $interventionUsagerAcceptedEvent)
    {
        $intervention = $interventionUsagerAcceptedEvent->getIntervention();

        $signalement = $intervention->getSignalement();
        $event = $this->eventManager->createEventEstimationSent(
            signalement: $signalement,
            title: 'Estimation '.$intervention->getEntreprise()->getNom(),
            description: 'L\'entreprise '.$intervention->getEntreprise()->getNom().' a envoyé une estimation',
            recipient: null,
            userId: Event::USER_ADMIN,
            userIdExcluded: null,
            label: 'Estimation acceptée',
            actionLabel: 'En savoir plus',
            actionLink: 'modalToOpen:view-estimation-'.$intervention->getId(),
        );
        $this->eventRepository->updateCreatedAt($event, $interventionUsagerAcceptedEvent->getCreatedAt());

        $event = $this->eventManager->createEventEstimationSent(
            signalement: $signalement,
            title: 'Estimation autre entreprise',
            description: 'L\'usager a accepté l\'estimation d\'une entreprise',
            recipient: null,
            userId: Event::USER_ALL,
            userIdExcluded: $intervention->getEntreprise()->getUser()->getId(),
            label: 'Estimation acceptée',
            actionLabel: null,
            actionLink: null,
        );
        $this->eventRepository->updateCreatedAt($event, $interventionUsagerAcceptedEvent->getCreatedAt());

        $event = $this->eventManager->createEventEstimationSent(
            signalement: $signalement,
            title: 'Estimation '.$intervention->getEntreprise()->getNom(),
            description: 'Vous avez envoyé une estimation à l\'usager.',
            recipient: null,
            userId: $intervention->getEntreprise()->getUser()->getId(),
            userIdExcluded: null,
            label: 'Estimation acceptée',
            actionLabel: 'En savoir plus',
            actionLink: 'modalToOpen:view-estimation-'.$intervention->getId(),
        );
        $this->eventRepository->updateCreatedAt($event, $interventionUsagerAcceptedEvent->getCreatedAt());

        $event = $this->eventManager->createEventEstimationSent(
            signalement: $signalement,
            title: 'Estimation '.$intervention->getEntreprise()->getNom(),
            description: 'L\'entreprise '.$intervention->getEntreprise()->getNom().' vous a envoyé une estimation',
            recipient: $intervention->getSignalement()->getEmailOccupant(),
            userId: null,
            userIdExcluded: null,
            label: 'Estimation acceptée',
            actionLabel: 'En savoir plus',
            actionLink: 'modalToOpen:estimation-accepted-'.$intervention->getId(),
        );
        $this->eventRepository->updateCreatedAt($event, $interventionUsagerAcceptedEvent->getCreatedAt());
    }
}
