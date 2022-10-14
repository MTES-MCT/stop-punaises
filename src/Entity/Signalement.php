<?php

namespace App\Entity;

use App\Repository\SignalementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SignalementRepository::class)]
class Signalement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::GUID)]
    private ?string $uuid = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(length: 10)]
    private ?string $codePostal = null;

    #[ORM\Column(length: 255)]
    private ?string $ville = null;

    #[ORM\Column(length: 20)]
    private ?string $typeLogement = null;

    #[ORM\Column(nullable: true)]
    private ?bool $construitAvant1948 = null;

    #[ORM\Column(length: 50)]
    private ?string $nomOccupant = null;

    #[ORM\Column(length: 50)]
    private ?string $prenomOccupant = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telephoneOccupant = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $emailOccupant = null;

    #[ORM\Column(length: 10)]
    private ?string $typeIntervention = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateIntervention = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $niveauInfestation = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $typeTraitement = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $nomBiocide = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $typeDiagnostic = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $nombrePiecesTraitees = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $delaiEntreInterventions = null;

    #[ORM\Column(nullable: true)]
    private ?bool $faitVisitePostTraitement = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateVisitePostTraitement = null;

    #[ORM\Column(nullable: true)]
    private ?int $prixFactureHT = null;

    #[ORM\ManyToOne(inversedBy: 'signalements')]
    private ?Entreprise $entreprise = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $localisationDansImmeuble = null;

    #[ORM\Column(length: 10)]
    private ?string $codeInsee = null;

    #[ORM\ManyToOne(inversedBy: 'signalements')]
    private ?Employe $agent = null;

    #[ORM\Column(length: 100)]
    private ?string $reference = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

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

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(string $codePostal): self
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    public function getTypeLogement(): ?string
    {
        return $this->typeLogement;
    }

    public function setTypeLogement(string $typeLogement): self
    {
        $this->typeLogement = $typeLogement;

        return $this;
    }

    public function isConstruitAvant1948(): ?bool
    {
        return $this->construitAvant1948;
    }

    public function setConstruitAvant1948(?bool $construitAvant1948): self
    {
        $this->construitAvant1948 = $construitAvant1948;

        return $this;
    }

    public function getNomOccupant(): ?string
    {
        return $this->nomOccupant;
    }

    public function setNomOccupant(string $nomOccupant): self
    {
        $this->nomOccupant = $nomOccupant;

        return $this;
    }

    public function getPrenomOccupant(): ?string
    {
        return $this->prenomOccupant;
    }

    public function setPrenomOccupant(string $prenomOccupant): self
    {
        $this->prenomOccupant = $prenomOccupant;

        return $this;
    }

    public function getTelephoneOccupant(): ?string
    {
        return $this->telephoneOccupant;
    }

    public function setTelephoneOccupant(?string $telephoneOccupant): self
    {
        $this->telephoneOccupant = $telephoneOccupant;

        return $this;
    }

    public function getEmailOccupant(): ?string
    {
        return $this->emailOccupant;
    }

    public function setEmailOccupant(?string $emailOccupant): self
    {
        $this->emailOccupant = $emailOccupant;

        return $this;
    }

    public function getTypeIntervention(): ?string
    {
        return $this->typeIntervention;
    }

    public function setTypeIntervention(string $typeIntervention): self
    {
        $this->typeIntervention = $typeIntervention;

        return $this;
    }

    public function getDateIntervention(): ?\DateTimeInterface
    {
        return $this->dateIntervention;
    }

    public function setDateIntervention(?\DateTimeInterface $dateIntervention): self
    {
        $this->dateIntervention = $dateIntervention;

        return $this;
    }

    public function getNiveauInfestation(): ?int
    {
        return $this->niveauInfestation;
    }

    public function setNiveauInfestation(?int $niveauInfestation): self
    {
        $this->niveauInfestation = $niveauInfestation;

        return $this;
    }

    public function getTypeTraitement(): ?string
    {
        return $this->typeTraitement;
    }

    public function setTypeTraitement(?string $typeTraitement): self
    {
        $this->typeTraitement = $typeTraitement;

        return $this;
    }

    public function getNomBiocide(): ?string
    {
        return $this->nomBiocide;
    }

    public function setNomBiocide(?string $nomBiocide): self
    {
        $this->nomBiocide = $nomBiocide;

        return $this;
    }

    public function getTypeDiagnostic(): ?string
    {
        return $this->typeDiagnostic;
    }

    public function setTypeDiagnostic(?string $typeDiagnostic): self
    {
        $this->typeDiagnostic = $typeDiagnostic;

        return $this;
    }

    public function getNombrePiecesTraitees(): ?int
    {
        return $this->nombrePiecesTraitees;
    }

    public function setNombrePiecesTraitees(?int $nombrePiecesTraitees): self
    {
        $this->nombrePiecesTraitees = $nombrePiecesTraitees;

        return $this;
    }

    public function getDelaiEntreInterventions(): ?int
    {
        return $this->delaiEntreInterventions;
    }

    public function setDelaiEntreInterventions(?int $delaiEntreInterventions): self
    {
        $this->delaiEntreInterventions = $delaiEntreInterventions;

        return $this;
    }

    public function isFaitVisitePostTraitement(): ?bool
    {
        return $this->faitVisitePostTraitement;
    }

    public function setFaitVisitePostTraitement(?bool $faitVisitePostTraitement): self
    {
        $this->faitVisitePostTraitement = $faitVisitePostTraitement;

        return $this;
    }

    public function getDateVisitePostTraitement(): ?\DateTimeInterface
    {
        return $this->dateVisitePostTraitement;
    }

    public function setDateVisitePostTraitement(?\DateTimeInterface $dateVisitePostTraitement): self
    {
        $this->dateVisitePostTraitement = $dateVisitePostTraitement;

        return $this;
    }

    public function getPrixFactureHT(): ?int
    {
        return $this->prixFactureHT;
    }

    public function setPrixFactureHT(?int $prixFactureHT): self
    {
        $this->prixFactureHT = $prixFactureHT;

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

    public function getLocalisationDansImmeuble(): ?string
    {
        return $this->localisationDansImmeuble;
    }

    public function setLocalisationDansImmeuble(?string $localisationDansImmeuble): self
    {
        $this->localisationDansImmeuble = $localisationDansImmeuble;

        return $this;
    }

    public function getCodeInsee(): ?string
    {
        return $this->codeInsee;
    }

    public function setCodeInsee(string $codeInsee): self
    {
        $this->codeInsee = $codeInsee;

        return $this;
    }

    public function getAgent(): ?Employe
    {
        return $this->agent;
    }

    public function setAgent(?Employe $agent): self
    {
        $this->agent = $agent;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
