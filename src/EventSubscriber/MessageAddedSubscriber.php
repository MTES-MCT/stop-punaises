<?php

namespace App\EventSubscriber;

use App\Event\MessageAddedEvent;
use App\Manager\EventManager;
use App\Service\Mailer\MailerProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MessageAddedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MailerProvider $mailerProvider,
        private EventManager $eventManager,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MessageAddedEvent::NAME => 'onMessageAdded',
        ];
    }

    public function onMessageAdded(MessageAddedEvent $messageAddedEvent)
    {
        $messageThread = $messageAddedEvent->getMessage()->getMessagesThread();
        $signalement = $messageThread->getSignalement();
        $entreprise = $messageThread->getEntreprise();
        $entrepriseName = $entreprise->getNom();
        $entrepriseEmail = $entreprise->getUser()->getEmail();

        if (null === $messageAddedEvent->getUser()) {
            $this->mailerProvider->sendNotificationToEntreprise($signalement, $entrepriseEmail);

            $this->eventManager->createEventMessage(
                messageThread: $messageThread,
                title: sprintf('Message avec %s', $entrepriseName),
                description: sprintf('L\'entreprise %s va vous contacter.', $entrepriseName),
                recipient: $signalement->getEmailOccupant()
            );
        } else {
            $this->mailerProvider->sendNotificationToUsager($signalement, $entrepriseName);

            $link = $this->urlGenerator->generate('app_suivi_usager_view_messages_thread', [
                'signalement_uuid' => $signalement->getUuid(),
                'thread_uuid' => $messageThread->getUuid(),
            ]);

            $this->eventManager->createEventMessage(
                messageThread: $messageThread,
                title: sprintf('Messages avec %s', $entrepriseName),
                description: sprintf('Vos échanges avec %s.', $entrepriseName),
                recipient: $signalement->getEmailOccupant(),
                actionLink: $link,
            );
        }
    }
}
