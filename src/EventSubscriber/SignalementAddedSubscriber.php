<?php

namespace App\EventSubscriber;

use App\Entity\Event;
use App\Event\SignalementAddedEvent;
use App\Manager\EventManager;
use App\Repository\EventRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SignalementAddedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EventManager $eventManager,
        private EventRepository $eventRepository,
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
        $event = $this->eventManager->createEventNewSignalement(
            signalement: $signalement,
            description: 'Votre signalement a bien été enregistré sur Stop Punaises.',
            recipient: $signalement->getEmailOccupant(),
            userId: null,
        );
        $this->eventRepository->updateCreatedAt($event, $signalementAddedEvent->getCreatedAt());

        $event = $this->eventManager->createEventNewSignalement(
            signalement: $signalement,
            description: 'Le signalement a bien été enregistré sur Stop Punaises.',
            recipient: null,
            userId: Event::USER_ALL,
        );
        $this->eventRepository->updateCreatedAt($event, $signalementAddedEvent->getCreatedAt());

        if ($signalement->isAutotraitement()) {
            $event = $this->eventManager->createEventProtocole(
                signalement: $signalement,
                recipient: $signalement->getEmailOccupant(),
                userId: null,
                pdfUrl: $signalementAddedEvent->getPdfUrl(),
                pdfSize: $signalementAddedEvent->getPdfSize(),
            );
            $this->eventRepository->updateCreatedAt($event, $signalementAddedEvent->getCreatedAt());

            $event = $this->eventManager->createEventProtocole(
                signalement: $signalement,
                recipient: null,
                userId: Event::USER_ALL,
                pdfUrl: null,
                pdfSize: null,
            );
            $this->eventRepository->updateCreatedAt($event, $signalementAddedEvent->getCreatedAt());
        }
    }
}
