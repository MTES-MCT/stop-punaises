<?php

namespace App\EventSubscriber;

use App\Entity\Event;
use App\Event\InterventionEntrepriseAllRefusedEvent;
use App\Manager\EventManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InterventionEntrepriseAllRefusedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EventManager $eventManager,
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
        $this->eventManager->createEventNoEntrepriseAvailable(
            signalement: $intervention->getSignalement(),
            description: 'Aucune entreprise n\'est en capacité de traiter cette demande',
            recipient: null,
            userId: Event::USER_ALL,
            actionLabel: null,
            actionLink: null,
        );
        $this->eventManager->createEventNoEntrepriseAvailable(
            signalement: $intervention->getSignalement(),
            description: 'Aucune entreprise n\'est en capacité de traiter votre demande',
            recipient: $intervention->getSignalement()->getEmailOccupant(),
            userId: null,
            actionLabel: 'En savoir plus',
            actionLink: 'modalToOpen:empty-estimations',
        );
    }
}
