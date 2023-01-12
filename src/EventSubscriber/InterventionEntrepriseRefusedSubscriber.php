<?php

namespace App\EventSubscriber;

use App\Entity\Event;
use App\Event\InterventionEntrepriseRefusedEvent;
use App\Manager\EventManager;
use App\Repository\EventRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InterventionEntrepriseRefusedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EventManager $eventManager,
        private EventRepository $eventRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InterventionEntrepriseRefusedEvent::NAME => 'onInterventionEntrepriseRefused',
        ];
    }

    public function onInterventionEntrepriseRefused(InterventionEntrepriseRefusedEvent $interventionEntrepriseRefusedEvent)
    {
        $intervention = $interventionEntrepriseRefusedEvent->getIntervention();
        $event = $this->eventManager->createEventSignalementRefusedByEntreprise(
            signalement: $intervention->getSignalement(),
            title: $intervention->getEntreprise()->getNom().' a refusé le signalement',
            description: 'L\'entreprise a refusé le signalement',
            recipient: null,
            userId: Event::USER_ADMIN,
        );
        $this->eventRepository->updateCreatedAt($event, $interventionEntrepriseRefusedEvent->getCreatedAt());

        $event = $this->eventManager->createEventSignalementRefusedByEntreprise(
            signalement: $intervention->getSignalement(),
            title: 'Signalement refusé',
            description: 'Vous avez refusé le signalement',
            recipient: null,
            userId: $interventionEntrepriseRefusedEvent->getUserId(),
        );
        $this->eventRepository->updateCreatedAt($event, $interventionEntrepriseRefusedEvent->getCreatedAt());
    }
}
