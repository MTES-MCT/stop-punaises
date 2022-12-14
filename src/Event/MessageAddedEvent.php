<?php

namespace App\Event;

use App\Entity\Message;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class MessageAddedEvent extends Event
{
    public const NAME = 'message.added';

    public function __construct(private Message $message, private ?User $user = null)
    {
    }

    public function getMessage(): Message
    {
        return $this->message;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
