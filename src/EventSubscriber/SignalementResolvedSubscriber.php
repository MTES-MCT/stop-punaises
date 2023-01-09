<?php

namespace App\EventSubscriber;

use App\Entity\Event;
use App\Event\SignalementResolvedEvent;
use App\Manager\EventManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SignalementResolvedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EventManager $eventManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SignalementResolvedEvent::NAME => 'onSignalementResolved',
        ];
    }

    public function onSignalementResolved(SignalementResolvedEvent $signalementResolvedEvent)
    {
        $signalement = $signalementResolvedEvent->getSignalement();

        // On ajoute l'événement de reminder pour
        // désactiver la précédente
        // et donc ne plus afficher la modale de demande de résolution
        $this->eventManager->createEventReminderAutotraitement(
            signalement: $signalement,
            description: 'Votre problème de punaises est-il résolu ?',
            recipient: $signalement->getEmailOccupant(),
            userId: null,
            label: null,
            actionLabel: null,
            modalToOpen: null,
        );

        $this->eventManager->createEventResolveSignalement(
            signalement: $signalement,
            description: 'L\'usager a indiqué que l\'infestation est résolue.',
            recipient: null,
            userId: Event::USER_ALL,
        );
        $this->eventManager->createEventResolveSignalement(
            signalement: $signalement,
            description: 'Vous avez résolu votre problème ! Merci d\'avoir utilisé Stop Punaises.',
            recipient: $signalement->getEmailOccupant(),
            userId: null,
        );
    }
}
