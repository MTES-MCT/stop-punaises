<?php

namespace App\Entity;

use App\Entity\Enum\Status;
use App\Repository\EntrepriseRepository;
use App\Validator as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EntrepriseRepository::class)]
#[AppAssert\EmailEntrepriseUnique]
class Entreprise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::GUID)]
    private ?string $uuid = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255, maxMessage: 'Le nom de l\'entreprise ne doit pas dépasser {{ limit }} caractères')]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50, maxMessage: 'Le numéro de SIRET ne doit pas dépasser {{ limit }} caractères')]
    private ?string $numeroSiret = null;

    #[ORM\Column(length: 20)]
    #[Assert\Regex(
        pattern: '/^(?:0|\(?\+33\)?\s?|0033\s?)[1-9](?:[\.\-\s]?\d\d){4}$/',
        match: true,
        message: 'Merci de saisir le numéro de téléphone au bon format'
    )]
    private ?string $telephone = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100, maxMessage: 'Le numéro de label ne doit pas dépasser {{ limit }} caractères')]
    private ?string $numeroLabel = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 255, maxMessage: 'L\'adresse e-mail ne doit pas dépasser {{ limit }} caractères')]
    private ?string $email = null;

    #[ORM\ManyToMany(targetEntity: Territoire::class, inversedBy: 'entreprises')]
    private Collection $territoires;

    #[ORM\OneToMany(mappedBy: 'entreprise', targetEntity: Signalement::class)]
    private Collection $signalements;

    #[ORM\OneToMany(mappedBy: 'entreprise', targetEntity: Employe::class)]
    private Collection $employes;

    #[ORM\OneToOne(targetEntity: User::class, mappedBy: 'entreprise')]
    private $user;

    #[ORM\OneToMany(mappedBy: 'entreprise', targetEntity: Intervention::class)]
    private Collection $interventions;

    #[ORM\OneToMany(mappedBy: 'entreprise', targetEntity: MessageThread::class)]
    private Collection $messagesThread;

    public function __construct()
    {
        $this->territoires = new ArrayCollection();
        $this->signalements = new ArrayCollection();
        $this->employes = new ArrayCollection();
        $this->interventions = new ArrayCollection();
        $this->messagesThread = new ArrayCollection();
        $this->uuid = uniqid();
    }

    public function __toString(): string
    {
        return $this->nom;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getNumeroSiret(): ?string
    {
        return $this->numeroSiret;
    }

    public function setNumeroSiret(string $numeroSiret): self
    {
        $this->numeroSiret = $numeroSiret;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getNumeroLabel(): ?string
    {
        return $this->numeroLabel;
    }

    public function setNumeroLabel(?string $numeroLabel): self
    {
        $this->numeroLabel = $numeroLabel;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection<int, Territoire>
     */
    public function getTerritoires(): Collection
    {
        return $this->territoires;
    }

    public function addTerritoire(Territoire $territoire): self
    {
        if (!$this->territoires->contains($territoire)) {
            $this->territoires->add($territoire);
        }

        return $this;
    }

    public function removeTerritoire(Territoire $territoire): self
    {
        $this->territoires->removeElement($territoire);

        return $this;
    }

    public function getTerritoireIds(): array
    {
        $result = [];
        $territoires = $this->getTerritoires();
        foreach ($territoires as $territoire) {
            $result[] = $territoire->getId();
        }

        return $result;
    }

    /**
     * @return Collection<int, Signalement>
     */
    public function getSignalements(): Collection
    {
        return $this->signalements;
    }

    public function addSignalement(Signalement $signalement): self
    {
        if (!$this->signalements->contains($signalement)) {
            $this->signalements->add($signalement);
            $signalement->setEntreprise($this);
        }

        return $this;
    }

    public function removeSignalement(Signalement $signalement): self
    {
        if ($this->signalements->removeElement($signalement)) {
            // set the owning side to null (unless already changed)
            if ($signalement->getEntreprise() === $this) {
                $signalement->setEntreprise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Employe>
     */
    public function getEmployes(): Collection
    {
        return $this->employes;
    }

    public function addEmploye(Employe $employe): self
    {
        if (!$this->employes->contains($employe)) {
            $this->employes->add($employe);
            $employe->setEntreprise($this);
        }

        return $this;
    }

    public function removeEmploye(Employe $employe): self
    {
        if ($this->employes->removeElement($employe)) {
            // set the owning side to null (unless already changed)
            if ($employe->getEntreprise() === $this) {
                $employe->setEntreprise(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Intervention>
     */
    public function getInterventions(): Collection
    {
        return $this->interventions;
    }

    public function addInterventions(Intervention $intervention): self
    {
        if (!$this->interventions->contains($intervention)) {
            $this->interventions->add($intervention);
            $intervention->setEntreprise($this);
        }

        return $this;
    }

    public function removeInterventions(Intervention $intervention): self
    {
        if ($this->interventions->removeElement($intervention)) {
            // set the owning side to null (unless already changed)
            if ($intervention->getEntreprise() === $this) {
                $intervention->setEntreprise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MessageThread>
     */
    public function getMessagesThread(): Collection
    {
        return $this->messagesThread;
    }

    public function addMessagesThread(MessageThread $messagesThread): self
    {
        if (!$this->messagesThread->contains($messagesThread)) {
            $this->messagesThread->add($messagesThread);
            $messagesThread->setEntreprise($this);
        }

        return $this;
    }

    public function removeMessagesThread(MessageThread $messagesThread): self
    {
        if ($this->messagesThread->removeElement($messagesThread)) {
            // set the owning side to null (unless already changed)
            if ($messagesThread->getEntreprise() === $this) {
                $messagesThread->setEntreprise(null);
            }
        }

        return $this;
    }

    public function isActive(): bool
    {
        return $this->user && Status::ACTIVE === $this->user->getStatus();
    }
}
