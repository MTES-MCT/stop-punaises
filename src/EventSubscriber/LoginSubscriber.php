<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Manager\UserManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginSubscriber implements EventSubscriberInterface
{
    public function __construct(private UserManager $userManager)
    {
    }

    public function onLoginSuccess(LoginSuccessEvent $event)
    {
        /** @var User $user */
        $user = $event->getUser();
        $user->setLastLogin(new \DateTimeImmutable());
        $this->userManager->save($user);
    }

    public static function getSubscribedEvents()
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }
}
