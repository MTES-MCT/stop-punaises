<?php

namespace App\Entity;

use App\Entity\Behaviour\TimestampableTrait;
use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ORM\HasLifecycleCallbacks()]
class Message
{
    use TimestampableTrait;

    public const DOMAIN_NAME = 'message';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $sender = null;

    #[ORM\Column(length: 50)]
    private ?string $recipient = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 10, minMessage: 'Votre message doit avoir {{ limit }} caractères minimum') ]
    private ?string $content = null;

    #[ORM\ManyToOne(inversedBy: 'messages', cascade: ['persist'])]
    private ?MessageThread $messagesThread = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSender(): ?string
    {
        return $this->sender;
    }

    public function setSender(string $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getRecipient(): ?string
    {
        return $this->recipient;
    }

    public function setRecipient(?string $recipient): self
    {
        $this->recipient = $recipient;

        return $this;
    }

    public function getMessagesThread(): ?MessageThread
    {
        return $this->messagesThread;
    }

    public function setMessagesThread(?MessageThread $messagesThread): self
    {
        $this->messagesThread = $messagesThread;

        return $this;
    }
}
