<?php

namespace App\EventSubscriber;

use App\Entity\Event;
use App\Event\InterventionEntrepriseAllRefusedEvent;
use App\Manager\EventManager;
use App\Repository\EventRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InterventionEntrepriseAllRefusedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EventManager $eventManager,
        private EventRepository $eventRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InterventionEntrepriseAllRefusedEvent::NAME => 'onInterventionEntrepriseAllRefused',
        ];
    }

    public function onInterventionEntrepriseAllRefused(InterventionEntrepriseAllRefusedEvent $interventionEntrepriseAllRefusedEvent)
    {
        $intervention = $interventionEntrepriseAllRefusedEvent->getIntervention();
        $event = $this->eventManager->createEventNoEntrepriseAvailable(
            signalement: $intervention->getSignalement(),
            description: 'Aucune entreprise n\'est en capacité de traiter cette demande',
            recipient: null,
            userId: Event::USER_ALL,
            actionLabel: null,
            actionLink: null,
        );
        $this->eventRepository->updateCreatedAt($event, $interventionEntrepriseAllRefusedEvent->getCreatedAt());

        $event = $this->eventManager->createEventNoEntrepriseAvailable(
            signalement: $intervention->getSignalement(),
            description: 'Aucune entreprise n\'est en capacité de traiter votre demande',
            recipient: $intervention->getSignalement()->getEmailOccupant(),
            userId: null,
            actionLabel: 'En savoir plus',
            actionLink: 'modalToOpen:empty-estimations',
        );
        $this->eventRepository->updateCreatedAt($event, $interventionEntrepriseAllRefusedEvent->getCreatedAt());
    }
}
