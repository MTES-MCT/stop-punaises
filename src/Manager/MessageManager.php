<?php

namespace App\Manager;

use App\Dto\MessageResponse;
use App\Entity\Message;
use App\Entity\User;
use App\Event\MessageAddedEvent;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MessageManager extends AbstractManager
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        protected ManagerRegistry $managerRegistry,
        protected string $entityName = Message::class
    ) {
        parent::__construct($managerRegistry, $entityName);
    }

    public function createMessageResponse(Message $message, ?User $user = null): MessageResponse
    {
        $this->save($message);
        $this->eventDispatcher->dispatch(new MessageAddedEvent($message, $user), MessageAddedEvent::NAME);

        return (new MessageResponse())
            ->setSender($message->getSender())
            ->setCreatedAt($message->getCreatedAt()->format('d/m/Y'))
            ->setContent($message->getContent());
    }
}
