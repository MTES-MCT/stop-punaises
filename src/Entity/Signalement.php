<?php

namespace App\Entity;

use App\Entity\Behaviour\ActivableTrait;
use App\Entity\Behaviour\TimestampableTrait;
use App\Entity\Enum\Declarant;
use App\Repository\SignalementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SignalementRepository::class)]
#[ORM\HasLifecycleCallbacks()]
class Signalement
{
    use ActivableTrait;
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::GUID)]
    private ?string $uuid = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(length: 10)]
    private ?string $codePostal = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ville = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $typeLogement = null;

    #[ORM\Column(nullable: true)]
    private ?bool $construitAvant1948 = null;

    #[ORM\Column(length: 50)]
    private ?string $nomOccupant = null;

    #[ORM\Column(length: 50)]
    private ?string $prenomOccupant = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Regex(
        pattern: '/^(\(0\))?[0-9]+$/',
        match: true,
        message: 'Merci de saisir le numéro de téléphone au bon format'
    )]
    private ?string $telephoneOccupant = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Email]
    private ?string $emailOccupant = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $typeIntervention = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateIntervention = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $niveauInfestation = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $typeTraitement = null;

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

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $codeInsee = null;

    #[ORM\ManyToOne(inversedBy: 'signalements')]
    private ?Employe $agent = null;

    #[ORM\Column(length: 100)]
    private ?string $reference = null;

    #[ORM\Column(type: 'string', enumType: Declarant::class)]
    private Declarant $declarant;

    #[ORM\Column(nullable: true)]
    private ?bool $autotraitement = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $superficie = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $dureeInfestation = null;

    #[ORM\Column(nullable: true)]
    private ?bool $infestationLogementsVoisins = null;

    #[ORM\Column(nullable: true)]
    private ?bool $piquresExistantes = null;

    #[ORM\Column(nullable: true)]
    private ?bool $piquresConfirmees = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $dejectionsDetails = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $oeufsEtLarvesDetails = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $punaisesDetails = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private $photos = [];

    #[ORM\ManyToOne(inversedBy: 'signalements')]
    private ?Territoire $territoire = null;

    #[ORM\Column(nullable: true)]
    private ?bool $locataire = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $nomProprietaire = null;

    #[ORM\Column(nullable: true)]
    private ?bool $logementSocial = null;

    #[ORM\Column(nullable: true)]
    private ?bool $allocataire = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $numeroAllocataire = null;

    public function __construct()
    {
        $this->uuid = Uuid::v4();
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

    public function getAdresseComplete(): string
    {
        return $this->getAdresse().' '.$this->getCodePostal().' '.$this->getVille();
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

    public function getNomCompletOccupant(): ?string
    {
        return $this->getPrenomOccupant().' '.$this->getNomOccupant();
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

    public function getTypeTraitement(): ?array
    {
        return $this->typeTraitement;
    }

    public function setTypeTraitement(?array $typeTraitement): self
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

    public function getDeclarant(): Declarant
    {
        return $this->declarant;
    }

    public function setDeclarant(Declarant $declarant): self
    {
        $this->declarant = $declarant;

        return $this;
    }

    public function isAutotraitement(): ?bool
    {
        return $this->autotraitement;
    }

    public function setAutotraitement(?bool $autotraitement): self
    {
        $this->autotraitement = $autotraitement;

        return $this;
    }

    public function getSuperficie(): ?string
    {
        return $this->superficie;
    }

    public function setSuperficie(string $superficie): self
    {
        $this->superficie = $superficie;

        return $this;
    }

    public function getDureeInfestation(): ?string
    {
        return $this->dureeInfestation;
    }

    public function setDureeInfestation(string $dureeInfestation): self
    {
        $this->dureeInfestation = $dureeInfestation;

        return $this;
    }

    public function isInfestationLogementsVoisins(): ?bool
    {
        return $this->infestationLogementsVoisins;
    }

    public function setInfestationLogementsVoisins(?bool $infestationLogementsVoisins): self
    {
        $this->infestationLogementsVoisins = $infestationLogementsVoisins;

        return $this;
    }

    public function isPiquresExistantes(): ?bool
    {
        return $this->piquresExistantes;
    }

    public function setPiquresExistantes(?bool $piquresExistantes): self
    {
        $this->piquresExistantes = $piquresExistantes;

        return $this;
    }

    public function isPiquresConfirmees(): ?bool
    {
        return $this->piquresConfirmees;
    }

    public function setPiquresConfirmees(?bool $piquresConfirmees): self
    {
        $this->piquresConfirmees = $piquresConfirmees;

        return $this;
    }

    public function getDejectionsDetails(): ?array
    {
        return $this->dejectionsDetails;
    }

    public function setDejectionsDetails(?array $dejectionsDetails): self
    {
        $this->dejectionsDetails = $dejectionsDetails;

        return $this;
    }

    public function getOeufsEtLarvesDetails(): ?array
    {
        return $this->oeufsEtLarvesDetails;
    }

    public function setOeufsEtLarvesDetails(?array $oeufsEtLarvesDetails): self
    {
        $this->oeufsEtLarvesDetails = $oeufsEtLarvesDetails;

        return $this;
    }

    public function getPunaisesDetails(): ?array
    {
        return $this->punaisesDetails;
    }

    public function setPunaisesDetails(?array $punaisesDetails): self
    {
        $this->punaisesDetails = $punaisesDetails;

        return $this;
    }

    public function getPhotos(): ?array
    {
        return $this->photos;
    }

    public function setPhotos(?array $photos): self
    {
        $this->photos = $photos;

        return $this;
    }

    public function getTerritoire(): ?Territoire
    {
        return $this->territoire;
    }

    public function setTerritoire(?Territoire $territoire): self
    {
        $this->territoire = $territoire;

        return $this;
    }

    public function isLocataire(): ?bool
    {
        return $this->locataire;
    }

    public function setLocataire(?bool $locataire): self
    {
        $this->locataire = $locataire;

        return $this;
    }

    public function getNomProprietaire(): ?string
    {
        return $this->nomProprietaire;
    }

    public function setNomProprietaire(?string $nomProprietaire): self
    {
        $this->nomProprietaire = $nomProprietaire;

        return $this;
    }

    public function isLogementSocial(): ?bool
    {
        return $this->logementSocial;
    }

    public function setLogementSocial(?bool $logementSocial): self
    {
        $this->logementSocial = $logementSocial;

        return $this;
    }

    public function isAllocataire(): ?bool
    {
        return $this->allocataire;
    }

    public function setAllocataire(?bool $allocataire): self
    {
        $this->allocataire = $allocataire;

        return $this;
    }

    public function getNumeroAllocataire(): ?string
    {
        return $this->numeroAllocataire;
    }

    public function setNumeroAllocataire(?string $numeroAllocataire): self
    {
        $this->numeroAllocataire = $numeroAllocataire;

        return $this;
    }
}
