<?php

namespace App\EventSubscriber;

use App\Entity\Event;
use App\Event\InterventionEntrepriseResolvedEvent;
use App\Manager\EventManager;
use App\Repository\EventRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InterventionEntrepriseResolvedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EventManager $eventManager,
        private EventRepository $eventRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InterventionEntrepriseResolvedEvent::NAME => 'onInterventionEntrepriseResolved',
        ];
    }

    public function onInterventionEntrepriseResolved(InterventionEntrepriseResolvedEvent $interventionEntrepriseResolvedEvent)
    {
        $intervention = $interventionEntrepriseResolvedEvent->getIntervention();
        $signalement = $intervention->getSignalement();
        $event = $this->eventManager->createEventSignalementResolvedByEntreprise(
            signalement: $signalement,
            title: 'Intervention faite',
            description: 'L\'entreprise '.$intervention->getEntreprise()->getNom().' a marqué le signalement comme traité',
            recipient: null,
            userId: Event::USER_ADMIN,
        );
        $this->eventRepository->updateCreatedAt($event, $interventionEntrepriseResolvedEvent->getCreatedAt());

        $event = $this->eventManager->createEventSignalementResolvedByEntreprise(
            signalement: $signalement,
            title: 'Intervention faite',
            description: 'Vous avez marqué le signalement comme traité',
            recipient: null,
            userId: $intervention->getEntreprise()->getUser()->getId(),
        );
        $this->eventRepository->updateCreatedAt($event, $interventionEntrepriseResolvedEvent->getCreatedAt());

        $event = $this->eventManager->createEventSignalementResolvedByEntreprise(
            signalement: $signalement,
            title: 'Traitement effectué',
            description: 'L\'entreprise '.$intervention->getEntreprise()->getNom().' a indiqué avoir traité votre domicile',
            recipient: $intervention->getSignalement()->getEmailOccupant(),
            userId: null,
        );
        $this->eventRepository->updateCreatedAt($event, $interventionEntrepriseResolvedEvent->getCreatedAt());
    }
}
