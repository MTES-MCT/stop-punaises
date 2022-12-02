<?php

namespace App\Entity;

use App\Repository\InterventionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InterventionRepository::class)]
class Intervention
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'interventions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Signalement $signalement = null;

    #[ORM\ManyToOne(inversedBy: 'accepted')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    #[ORM\Column(nullable: true)]
    private ?bool $accepted = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaireRefus = null;

    #[ORM\Column(nullable: true)]
    private ?int $montantEstimation = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaireEstimation = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $choiceByEntrepriseAt = null;

    #[ORM\Column(nullable: true)]
    private ?bool $acceptedByUsager = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $choiceByUsagerAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSignalement(): ?Signalement
    {
        return $this->signalement;
    }

    public function setSignalement(?Signalement $signalement): self
    {
        $this->signalement = $signalement;

        return $this;
    }

    public function getEntreprise(): ?Entreprise
    {
        return $this->entreprise;
    }

    public function setEntreprise(?Entreprise $entreprise): self
    {
        $this->entreprise = $entreprise;

        return $this;
    }

    public function isAccepted(): ?bool
    {
        return $this->accepted;
    }

    public function setAccepted(?bool $accepted): self
    {
        $this->accepted = $accepted;

        return $this;
    }

    public function getCommentaireRefus(): ?string
    {
        return $this->commentaireRefus;
    }

    public function setCommentaireRefus(?string $commentaireRefus): self
    {
        $this->commentaireRefus = $commentaireRefus;

        return $this;
    }

    public function getMontantEstimation(): ?int
    {
        return $this->montantEstimation;
    }

    public function setMontantEstimation(?int $montantEstimation): self
    {
        $this->montantEstimation = $montantEstimation;

        return $this;
    }

    public function getCommentaireEstimation(): ?string
    {
        return $this->commentaireEstimation;
    }

    public function setCommentaireEstimation(?string $commentaireEstimation): self
    {
        $this->commentaireEstimation = $commentaireEstimation;

        return $this;
    }

    public function getChoiceByEntrepriseAt(): ?\DateTimeImmutable
    {
        return $this->choiceByEntrepriseAt;
    }

    public function setChoiceByEntrepriseAt(?\DateTimeImmutable $choiceByEntrepriseAt): self
    {
        $this->choiceByEntrepriseAt = $choiceByEntrepriseAt;

        return $this;
    }

    public function isAcceptedByUsager(): ?bool
    {
        return $this->acceptedByUsager;
    }

    public function setAcceptedByUsager(?bool $acceptedByUsager): self
    {
        $this->acceptedByUsager = $acceptedByUsager;

        return $this;
    }

    public function getChoiceByUsagerAt(): ?\DateTimeImmutable
    {
        return $this->choiceByUsagerAt;
    }

    public function setChoiceByUsagerAt(?\DateTimeImmutable $choiceByUsagerAt): self
    {
        $this->choiceByUsagerAt = $choiceByUsagerAt;

        return $this;
    }
}
