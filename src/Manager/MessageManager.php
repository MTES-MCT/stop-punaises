<?php

namespace App\Manager;

use App\Dto\MessageResponse;
use App\Entity\Message;
use Doctrine\Persistence\ManagerRegistry;

class MessageManager extends AbstractManager
{
    public function __construct(
        protected ManagerRegistry $managerRegistry,
        protected string $entityName = Message::class
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->entityName = $entityName;
    }

    public function createMessageResponse(Message $message): MessageResponse
    {
        $this->save($message);

        return (new MessageResponse())
            ->setSender($message->getSender())
            ->setCreatedAt($message->getCreatedAt()->format('d/m/Y'))
            ->setContent($message->getContent());
    }
}
