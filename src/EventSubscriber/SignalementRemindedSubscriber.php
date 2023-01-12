<?php

namespace App\EventSubscriber;

use App\Entity\Event;
use App\Event\SignalementRemindedEvent;
use App\Manager\EventManager;
use App\Repository\EventRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SignalementRemindedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EventManager $eventManager,
        private EventRepository $eventRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SignalementRemindedEvent::NAME => 'onSignalementReminded',
        ];
    }

    public function onSignalementReminded(SignalementRemindedEvent $signalementRemindedEvent)
    {
        $signalement = $signalementRemindedEvent->getSignalement();
        $event = $this->eventManager->createEventReminderAutotraitement(
            signalement: $signalement,
            description: 'Votre problème de punaises est-il résolu ?',
            recipient: $signalement->getEmailOccupant(),
            userId: null,
            label: 'Nouveau',
            actionLabel: 'En savoir plus',
            modalToOpen: 'probleme-resolu',
        );
        $this->eventRepository->updateCreatedAt($event, $signalementRemindedEvent->getCreatedAt());

        $event = $this->eventManager->createEventReminderAutotraitement(
            signalement: $signalement,
            description: 'L\'email de suivi post-traitement a été envoyé à l\'usager',
            recipient: null,
            userId: Event::USER_ADMIN,
            label: null,
            actionLabel: null,
            modalToOpen: null,
        );
        $this->eventRepository->updateCreatedAt($event, $signalementRemindedEvent->getCreatedAt());
    }
}
