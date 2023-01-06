<?php

namespace App\Entity;

use App\Entity\Behaviour\ActivableTrait;
use App\Entity\Behaviour\TimestampableTrait;
use App\Repository\EventRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\HasLifecycleCallbacks()]
class Event
{
    use ActivableTrait;
    use TimestampableTrait;

    public const DOMAIN_ADMIN_NOTICE = 'admin-notice';
    public const DOMAIN_CLOSE_SIGNALEMENT = 'close-signalement';
    public const DOMAIN_ESTIMATIONS_ALL_REFUSED = 'estimations-all-refused';
    public const DOMAIN_MESSAGE = 'message';
    public const DOMAIN_NEW_SIGNALEMENT = 'new-signalement';
    public const DOMAIN_NO_ENTREPRISE_AVAILABLE = 'no-entreprise-available';
    public const DOMAIN_PROTOCOLE = 'protocole-autotraitement';
    public const DOMAIN_REMINDER_AUTOTRAITEMENT = 'reminder-autotraitement';
    public const DOMAIN_REMINDER_PRO = 'reminder-pro';
    public const DOMAIN_RESOLVE_SIGNALEMENT = 'resolve-signalement';
    public const DOMAIN_SWITCH_TRAITEMENT = 'switch-traitement';

    public const USER_ADMIN = -1;
    public const USER_ALL = 0;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $label = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $actionLink = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $actionLabel = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $entityName = null;

    #[ORM\Column(nullable: true)]
    private ?string $entityUuid = null;

    #[ORM\Column(length: 255)]
    private ?string $domain = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $recipient = null;

    #[ORM\Column(nullable: true, options: ['comment' => 'null if usager, -1 if admin, 0 if all, id if entreprise'])]
    private ?int $userId = null;

    #[ORM\Column(nullable: true)]
    private ?int $userIdExcluded = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getActionLink(): ?string
    {
        return $this->actionLink;
    }

    public function setActionLink(?string $actionLink): self
    {
        $this->actionLink = $actionLink;

        return $this;
    }

    public function getActionLabel(): ?string
    {
        return $this->actionLabel;
    }

    public function setActionLabel(?string $actionLabel): self
    {
        $this->actionLabel = $actionLabel;

        return $this;
    }

    public function getEntityName(): ?string
    {
        return $this->entityName;
    }

    public function setEntityName(?string $entityName): self
    {
        $this->entityName = $entityName;

        return $this;
    }

    public function getEntityUuid(): ?string
    {
        return $this->entityUuid;
    }

    public function setEntityUuid(?string $entityUuid): self
    {
        $this->entityUuid = $entityUuid;

        return $this;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): self
    {
        $this->domain = $domain;

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

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getUserIdExcluded(): ?int
    {
        return $this->userIdExcluded;
    }

    public function setUserIdExcluded(?int $userIdExcluded): self
    {
        $this->userIdExcluded = $userIdExcluded;

        return $this;
    }
}
