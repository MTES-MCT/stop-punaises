<?php

namespace App\EventSubscriber;

use App\Entity\Event;
use App\Entity\MessageThread;
use App\Entity\Signalement;
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

        $link = $this->urlGenerator->generate('app_suivi_usager_view_messages_thread', [
            'signalement_uuid' => $signalement->getUuid(),
            'thread_uuid' => $messageThread->getUuid(),
        ]);

        if (null === $messageAddedEvent->getUser()) {
            $this->mailerProvider->sendNotificationToEntreprise($signalement, $entrepriseEmail);
            $this->eventManager->createEventMessage(
                messageThread: $messageThread,
                title: \sprintf('Messages avec %s', $entrepriseName),
                description: \sprintf('L\'entreprise %s va vous contacter.', $entrepriseName),
                recipient: $signalement->getEmailOccupant(),
            );
            $this->eventManager->createEventMessage(
                messageThread: $messageThread,
                title: \sprintf('Messages avec %s', $entrepriseName),
                description: \sprintf('Echanges avec %s.', $entrepriseName),
                recipient: null,
                userId: Event::USER_ADMIN,
                actionLink: $link,
            );
            $this->eventManager->createEventMessage(
                messageThread: $messageThread,
                title: 'Message avec l\'usager',
                description: \sprintf('Vos échanges avec %s.', $signalement->getNomCompletOccupant()),
                recipient: $entrepriseEmail,
                userId: $entreprise->getUser()->getId(),
                actionLink: 'link-send-message',
            );
        } else {
            $this->mailerProvider->sendNotificationToUsager($signalement, $entrepriseName);

            $this->eventManager->createEventMessage(
                messageThread: $messageThread,
                title: \sprintf('Messages avec %s', $entrepriseName),
                description: \sprintf('Vos échanges avec %s.', $entrepriseName),
                recipient: $signalement->getEmailOccupant(),
                actionLink: $link,
            );
            $this->eventManager->createEventMessage(
                messageThread: $messageThread,
                title: \sprintf('Messages avec %s', $entrepriseName),
                description: \sprintf('Echanges avec %s.', $entrepriseName),
                recipient: null,
                userId: Event::USER_ADMIN,
                actionLink: $link,
            );

            if (!$this->messageEventExists($messageThread)) {
                $this->eventManager->createEventMessage(
                    messageThread: $messageThread,
                    title: 'Message avec l\'usager',
                    description: \sprintf('Vos échanges avec %s.', $signalement->getNomCompletOccupant()),
                    recipient: $entrepriseEmail,
                    userId: $entreprise->getUser()->getId(),
                    actionLink: 'link-send-message',
                );
            }
        }
    }

    private function messageEventExists(MessageThread $messageThread): bool
    {
        $entreprise = $messageThread->getEntreprise();
        $signalement = $messageThread->getSignalement();
        $event = $this->eventManager->findOneBy([
            'domain' => Event::DOMAIN_MESSAGE,
            'title' => 'Message avec l\'usager',
            'description' => \sprintf('Vos échanges avec %s.', $entreprise->getNom()),
            'entityName' => Signalement::class,
            'entityUuid' => $signalement->getUuid(),
        ]);

        return $event instanceof Event;
    }
}
