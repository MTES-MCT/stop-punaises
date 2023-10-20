<?php

namespace App\Entity;

use App\Repository\EntreprisePubliqueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EntreprisePubliqueRepository::class)]
class EntreprisePublique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $url = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 3)]
    private ?string $zip = null;

    #[ORM\Column]
    private ?bool $isIntervention = null;

    #[ORM\Column]
    private ?bool $isDetectionCanine = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isProOnly = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getZip(): ?string
    {
        return $this->zip;
    }

    public function setZip(string $zip): self
    {
        $this->zip = $zip;

        return $this;
    }

    public function getIsIntervention(): bool
    {
        return $this->isIntervention;
    }

    public function setIsIntervention(bool $isIntervention): self
    {
        $this->isIntervention = $isIntervention;

        return $this;
    }

    public function getIsDetectionCanine(): bool
    {
        return $this->isDetectionCanine;
    }

    public function setIsDetectionCanine(bool $isDetectionCanine): self
    {
        $this->isDetectionCanine = $isDetectionCanine;

        return $this;
    }

    public function getIsProOnly(): ?bool
    {
        return $this->isProOnly;
    }

    public function setIsProOnly(?bool $isProOnly): self
    {
        $this->isProOnly = $isProOnly;

        return $this;
    }
}
