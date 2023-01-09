<?php

namespace App\EventSubscriber;

use App\Entity\Event;
use App\Event\SignalementAddedEvent;
use App\Manager\EventManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SignalementAddedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EventManager $eventManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SignalementAddedEvent::NAME => 'onSignalementAdded',
        ];
    }

    public function onSignalementAdded(SignalementAddedEvent $signalementAddedEvent)
    {
        $signalement = $signalementAddedEvent->getSignalement();
        $this->eventManager->createEventNewSignalement(
            signalement: $signalement,
            description: 'Votre signalement a bien été enregistré sur Stop Punaises.',
            recipient: $signalement->getEmailOccupant(),
            userId: null,
        );
        $this->eventManager->createEventNewSignalement(
            signalement: $signalement,
            description: 'Le signalement a bien été enregistré sur Stop Punaises.',
            recipient: null,
            userId: Event::USER_ALL,
        );

        if ($signalement->isAutotraitement()) {
            $this->eventManager->createEventProtocole(
                signalement: $signalement,
                recipient: $signalement->getEmailOccupant(),
                userId: null,
                pdfUrl: $signalementAddedEvent->getPdfUrl(),
            );
            $this->eventManager->createEventProtocole(
                signalement: $signalement,
                recipient: null,
                userId: Event::USER_ALL,
                pdfUrl: null,
            );
        }
    }
}
