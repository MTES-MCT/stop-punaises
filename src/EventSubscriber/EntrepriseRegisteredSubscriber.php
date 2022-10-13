<?php

namespace App\EventSubscriber;

use App\Event\EntrepriseRegisteredEvent;
use App\Manager\UserManager;
use App\Service\Mailer\MailerProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EntrepriseRegisteredSubscriber implements EventSubscriberInterface
{
    public function __construct(private MailerProviderInterface $mailerProvider,
                                private UserManager $userManager
    ) {
    }

    public function onEntrepriseRegisteredEvent(EntrepriseRegisteredEvent $event): void
    {
        $this->userManager->createFrom($event->getEntreprise());
        $this->mailerProvider->sendActivateMessage($event->getUser());
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntrepriseRegisteredEvent::NAME => 'onEntrepriseRegisteredEvent',
        ];
    }
}
