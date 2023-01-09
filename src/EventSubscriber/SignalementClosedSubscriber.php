<?php

namespace App\EventSubscriber;

use App\Entity\Event;
use App\Event\SignalementClosedEvent;
use App\Manager\EventManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SignalementClosedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EventManager $eventManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SignalementClosedEvent::NAME => 'onSignalementClosed',
        ];
    }

    public function onSignalementClosed(SignalementClosedEvent $signalementClosedEvent)
    {
        $signalement = $signalementClosedEvent->getSignalement();

        $this->eventManager->createEventCloseSignalement(
            signalement: $signalement,
            description: 'L\'usager a mis fin à la procédure',
            recipient: null,
            userId: Event::USER_ALL,
        );
        $this->eventManager->createEventCloseSignalement(
            signalement: $signalement,
            description: 'Vous avez mis fin à la procédure. Merci d\'avoir utilisé Stop Punaises.',
            recipient: $signalement->getEmailOccupant(),
            userId: null,
        );
    }
}
