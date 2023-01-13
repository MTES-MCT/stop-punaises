<?php

namespace App\EventSubscriber;

use App\Entity\Event;
use App\Event\InterventionEntrepriseAcceptedEvent;
use App\Manager\EventManager;
use App\Repository\EventRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InterventionEntrepriseAcceptedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EventManager $eventManager,
        private EventRepository $eventRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InterventionEntrepriseAcceptedEvent::NAME => 'onInterventionEntrepriseAccepted',
        ];
    }

    public function onInterventionEntrepriseAccepted(InterventionEntrepriseAcceptedEvent $interventionEntrepriseAcceptedEvent)
    {
        $intervention = $interventionEntrepriseAcceptedEvent->getIntervention();
        $event = $this->eventManager->createEventSignalementAcceptedByEntreprise(
            signalement: $intervention->getSignalement(),
            title: $intervention->getEntreprise()->getNom().' a accepté le signalement',
            description: 'L\'entreprise a accepté le signalement',
            recipient: null,
            userId: Event::USER_ADMIN,
        );
        $this->eventRepository->updateCreatedAt($event, $interventionEntrepriseAcceptedEvent->getCreatedAt());

        $event = $this->eventManager->createEventSignalementAcceptedByEntreprise(
            signalement: $intervention->getSignalement(),
            title: 'Signalement accepté',
            description: 'Vous avez accepté le signalement',
            recipient: null,
            userId: $interventionEntrepriseAcceptedEvent->getUserId(),
        );
        $this->eventRepository->updateCreatedAt($event, $interventionEntrepriseAcceptedEvent->getCreatedAt());

        // On supprime un éventuel événement qui disait que les estimations étaient toutes refusées
        $this->eventManager->setPreviousInactive(
            signalement: $intervention->getSignalement(),
            domain: Event::DOMAIN_ESTIMATIONS_ALL_REFUSED,
            recipient: null,
            userId: Event::USER_ALL,
        );
        $this->eventManager->setPreviousInactive(
            signalement: $intervention->getSignalement(),
            domain: Event::DOMAIN_ESTIMATIONS_ALL_REFUSED,
            recipient: $intervention->getSignalement()->getEmailOccupant(),
            userId: null,
        );
    }
}
