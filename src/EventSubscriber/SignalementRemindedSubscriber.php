<?php

namespace App\EventSubscriber;

use App\Entity\Event;
use App\Event\SignalementRemindedEvent;
use App\Manager\EventManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SignalementRemindedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EventManager $eventManager,
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
        $this->eventManager->createEventReminderAutotraitement(
            signalement: $signalement,
            description: 'Votre problème de punaises est-il résolu ?',
            recipient: $signalement->getEmailOccupant(),
            userId: null,
            label: 'Nouveau',
            actionLabel: 'En savoir plus',
            modalToOpen: 'probleme-resolu',
        );
        $this->eventManager->createEventReminderAutotraitement(
            signalement: $signalement,
            description: 'L\'email de suivi post-traitement a été envoyé à l\'usager',
            recipient: null,
            userId: Event::USER_ADMIN,
            label: null,
            actionLabel: null,
            modalToOpen: null,
        );
    }
}
