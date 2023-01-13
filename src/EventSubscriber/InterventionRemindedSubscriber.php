<?php

namespace App\EventSubscriber;

use App\Entity\Event;
use App\Event\InterventionRemindedEvent;
use App\Manager\EventManager;
use App\Repository\EventRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InterventionRemindedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EventManager $eventManager,
        private EventRepository $eventRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InterventionRemindedEvent::NAME => 'onInterventionReminded',
        ];
    }

    public function onInterventionReminded(InterventionRemindedEvent $interventionRemindedEvent)
    {
        $intervention = $interventionRemindedEvent->getIntervention();
        $event = $this->eventManager->createEventReminderPro(
            signalement: $intervention->getSignalement(),
            description: 'Votre problème de punaises est-il résolu ?',
            recipient: $intervention->getSignalement()->getEmailOccupant(),
            userId: null,
            label: 'Nouveau',
            actionLabel: 'En savoir plus',
            modalToOpen: 'probleme-resolu-pro',
        );
        $this->eventRepository->updateCreatedAt($event, $interventionRemindedEvent->getCreatedAt());

        $event = $this->eventManager->createEventReminderPro(
            signalement: $intervention->getSignalement(),
            description: 'L\'email de suivi post-traitement a été envoyé à l\'usager',
            recipient: null,
            userId: Event::USER_ADMIN,
            label: null,
            actionLabel: null,
            modalToOpen: null,
        );
        $this->eventRepository->updateCreatedAt($event, $interventionRemindedEvent->getCreatedAt());
    }
}
