<?php

namespace App\EventSubscriber;

use App\Entity\Event;
use App\Event\SignalementSwitchedEvent;
use App\Manager\EventManager;
use App\Repository\EventRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SignalementSwitchedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EventManager $eventManager,
        private EventRepository $eventRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SignalementSwitchedEvent::NAME_AUTOTRAITEMENT => 'onSignalementSwitchedAutotraitement',
            SignalementSwitchedEvent::NAME_PRO => 'onSignalementSwitchedPro',
        ];
    }

    public function onSignalementSwitchedPro(SignalementSwitchedEvent $signalementSwitchedEvent)
    {
        $signalement = $signalementSwitchedEvent->getSignalement();
        $event = $this->eventManager->createEventSwitchTraitement(
            signalement: $signalement,
            description: 'Votre signalement a été transmis aux entreprises labellisées. Elles vous contacteront au plus vite.',
            recipient: $signalement->getEmailOccupant(),
            userId: null,
        );
        $this->eventRepository->updateCreatedAt($event, $signalementSwitchedEvent->getCreatedAt());

        $event = $this->eventManager->createEventSwitchTraitement(
            signalement: $signalement,
            description: 'Le signalement a été passé en traitement professionnel.',
            recipient: null,
            userId: Event::USER_ALL,
        );
        $this->eventRepository->updateCreatedAt($event, $signalementSwitchedEvent->getCreatedAt());
    }

    public function onSignalementSwitchedAutotraitement(SignalementSwitchedEvent $signalementSwitchedEvent)
    {
        $signalement = $signalementSwitchedEvent->getSignalement();
        $event = $this->eventManager->createEventSwitchTraitement(
            signalement: $signalement,
            description: 'Votre signalement a été passé en auto-traitement.',
            recipient: $signalement->getEmailOccupant(),
            userId: null,
        );
        $this->eventRepository->updateCreatedAt($event, $signalementSwitchedEvent->getCreatedAt());

        $event = $this->eventManager->createEventSwitchTraitement(
            signalement: $signalement,
            description: 'Le signalement a été passé en auto-traitement.',
            recipient: null,
            userId: Event::USER_ALL,
        );
        $this->eventRepository->updateCreatedAt($event, $signalementSwitchedEvent->getCreatedAt());
    }
}
