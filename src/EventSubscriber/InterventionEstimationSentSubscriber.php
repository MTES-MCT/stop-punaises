<?php

namespace App\EventSubscriber;

use App\Entity\Event;
use App\Event\InterventionEstimationSentEvent;
use App\Manager\EventManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InterventionEstimationSentSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EventManager $eventManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InterventionEstimationSentEvent::NAME => 'onInterventionEstimationSent',
        ];
    }

    public function onInterventionEstimationSent(InterventionEstimationSentEvent $interventionEstimationSentEvent)
    {
        $intervention = $interventionEstimationSentEvent->getIntervention();
        $signalement = $intervention->getSignalement();
        $this->eventManager->createEventEstimationSent(
            signalement: $signalement,
            title: 'Estimation '.$intervention->getEntreprise()->getNom(),
            description: 'L\'entreprise '.$intervention->getEntreprise()->getNom().' a envoyé une estimation',
            recipient: null,
            userId: Event::USER_ADMIN,
            userIdExcluded: null,
            label: 'Nouveau',
            actionLabel: 'En savoir plus',
            actionLink: 'modalToOpen:view-estimation-'.$intervention->getId(),
        );
        $this->eventManager->createEventEstimationSent(
            signalement: $signalement,
            title: 'Estimation '.$intervention->getEntreprise()->getNom(),
            description: 'Vous avez envoyé une estimation à l\'usager.',
            recipient: null,
            userId: $interventionEstimationSentEvent->getUserId(),
            userIdExcluded: null,
            label: 'Nouveau',
            actionLabel: 'En savoir plus',
            actionLink: 'modalToOpen:view-estimation-'.$intervention->getId(),
        );
        $this->eventManager->createEventEstimationSent(
            signalement: $signalement,
            title: 'Estimation '.$intervention->getEntreprise()->getNom(),
            description: 'L\'entreprise '.$intervention->getEntreprise()->getNom().' vous a envoyé une estimation',
            recipient: $intervention->getSignalement()->getEmailOccupant(),
            userId: null,
            userIdExcluded: null,
            label: 'Nouveau',
            actionLabel: 'En savoir plus',
            actionLink: 'modalToOpen:choice-estimation-'.$intervention->getId(),
        );
    }
}
