<?php

namespace App\Factory;

use App\Entity\Message;
use App\Entity\MessageThread;

class MessageFactory
{
    public function createInstanceFrom(
        MessageThread $messageThread,
        string $sender,
        string $recipient,
        string $message
    ): Message {
        return (new Message())
            ->setSender($sender)
            ->setRecipient($recipient)
            ->setContent($message)
            ->setMessagesThread($messageThread);
    }
}
