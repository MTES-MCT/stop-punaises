<?php

namespace App\Entity;

use App\Entity\Behaviour\ActivableTrait;
use App\Entity\Behaviour\TimestampableTrait;
use App\Entity\Enum\Declarant;
use App\Entity\Enum\PlaceType;
use App\Entity\Enum\SignalementType;
use App\Repository\SignalementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: SignalementRepository::class)]
#[ORM\HasLifecycleCallbacks()]
class Signalement
{
    use ActivableTrait;
    use TimestampableTrait;
    public const DEFAULT_TIMEZONE = 'Europe/Paris';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::GUID)]
    private ?string $uuid = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank(
        message: 'Veuillez renseigner une adresse.',
        groups: ['front_add_signalement_logement', 'front_add_signalement_erp']
    )]
    private ?string $adresse = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(
        message: 'Veuillez renseigner le code postal.',
        groups: ['front_add_signalement_logement', 'front_add_signalement_transport', 'front_add_signalement_erp']
    )]
    #[Assert\Regex(
        pattern: '/^[0-9]{5}$/',
        message: 'Veuillez utiliser un code postal valide',
    )]
    private ?string $codePostal = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank(
        message: 'Veuillez renseigner la ville.',
        groups: ['front_add_signalement_logement', 'front_add_signalement_transport', 'front_add_signalement_erp']
    )]
    private ?string $ville = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $typeLogement = null;

    #[ORM\Column(nullable: true)]
    private ?bool $construitAvant1948 = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    #[Assert\NotBlank(
        message: 'Veuillez renseigner votre nom.',
        groups: ['front_add_signalement_logement', 'back_add_signalement_logement']
    )]
    private ?string $nomOccupant = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    #[Assert\NotBlank(
        message: 'Veuillez renseigner votre prenom.',
        groups: ['front_add_signalement_logement', 'back_add_signalement_logement']
    )]
    private ?string $prenomOccupant = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Regex(
        pattern: '/^(?:0|\(?\+33\)?\s?|0033\s?)[1-9](?:[\.\-\s]?\d\d){4}$/',
        match: true,
        message: 'Merci de saisir le numéro de téléphone au bon format'
    )]
    #[Assert\NotBlank(
        message: 'Veuillez renseigner votre numéro de téléphone.',
        groups: ['front_add_signalement_logement']
    )]
    private ?string $telephoneOccupant = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    #[Email(mode : Email::VALIDATION_MODE_STRICT, message: 'Veuillez renseigner un email valide.', )]
    #[Assert\NotBlank(message: 'Veuillez renseigner votre e-mail.', groups: ['front_add_signalement_logement'])]
    private ?string $emailOccupant = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $typeIntervention = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateIntervention = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    #[Assert\Range(
        min: 0,
        max: 4,
        notInRangeMessage: 'Le niveau d\'infestation doit être compris entre 1 et 4.',
        groups: ['front_add_signalement_logement']
    )]
    #[Assert\NotBlank(
        message: 'Veuillez renseigner un niveau d\'infestation.',
        groups: ['front_add_signalement_logement']
    )]
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
    #[Assert\Regex(pattern: '/^[0-9][0-9A-Za-z][0-9]{3}$/', message: 'Le code insee doit être composé de 5 caractères.')]
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
    #[Assert\NotBlank(
        message: 'Veuillez renseigner une superficie.',
        groups: ['front_add_signalement_logement']
    )]
    #[Assert\Regex(
        pattern: '/^[0-9]{1,5}$/',
        message: 'Veuillez renseigner une superficie valide',
    )]
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
    private array $photos = [];

    #[ORM\ManyToOne(inversedBy: 'signalements')]
    private ?Territoire $territoire = null;

    #[ORM\OneToMany(mappedBy: 'signalement', targetEntity: Intervention::class)]
    private Collection $interventions;

    #[ORM\Column(nullable: true)]
    private ?bool $locataire = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    #[Assert\NotBlank(message: 'Veuillez renseigner le nom de l\'établissement.', groups: ['front_add_signalement_erp'])]
    private ?string $nomProprietaire = null;

    #[ORM\Column(nullable: true)]
    private ?bool $logementSocial = null;

    #[ORM\Column(nullable: true)]
    private ?bool $allocataire = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    private ?string $numeroAllocataire = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $reminderAutotraitementAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $resolvedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $switchedTraitementAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $closedAt = null;

    #[ORM\OneToMany(mappedBy: 'signalement', targetEntity: MessageThread::class)]
    private Collection $messagesThread;

    #[ORM\Column(type: Types::GUID, nullable: true)]
    private ?string $uuidPublic = null;

    #[ORM\Column(type: 'json')]
    private array $geoloc = [];

    #[ORM\Column(type: 'string', enumType: SignalementType::class)]
    private SignalementType $type;

    #[ORM\Column(nullable: true)]
    #[Assert\NotBlank(message: 'Veuillez renseigner la date.', groups: ['front_add_signalement_transport', 'front_add_signalement_erp'])]
    #[Assert\LessThan(
        value: new \DateTime(),
        message: 'La date renseignée n\'est pas encore passée, veuillez renseigner une nouvelle date.',
        groups: ['front_add_signalement_transport', 'front_add_signalement_erp']
    )]
    private ?\DateTimeImmutable $punaisesViewedAt = null;

    #[ORM\Column(type: 'string', enumType: PlaceType::class, nullable: true)]
    #[Assert\NotBlank(message: 'Veuillez renseigner le type de transport', groups: ['front_add_signalement_transport'])]
    #[Assert\NotBlank(message: 'Veuillez selectionner le type d\'établissement', groups: ['front_add_signalement_erp'])]
    private ?PlaceType $placeType = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    private ?string $transportLineNumber = null;

    #[ORM\Column(nullable: true)]
    #[Assert\NotNull(message: 'Veuillez indiquer si vous avez prévenu la compagnie de transport.', groups: ['front_add_signalement_transport'])]
    #[Assert\NotNull(message: 'Veuillez indiquer si vous avez prévenu l\'établissement.', groups: ['front_add_signalement_erp'])]
    private ?bool $isPlaceAvertie = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Length(max: 500)]
    #[Assert\Length(min: 10, minMessage: 'Merci de proposer une description (minimum 10 caractères).', groups: ['front_add_signalement_transport', 'front_add_signalement_erp'])]
    private ?string $autresInformations;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    #[Assert\NotBlank(message: 'Veuillez renseigner votre nom.', groups: ['front_add_signalement_transport', 'front_add_signalement_erp'])]
    private ?string $nomDeclarant = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    #[Assert\NotBlank(message: 'Veuillez renseigner votre prénom.', groups: ['front_add_signalement_transport', 'front_add_signalement_erp'])]
    private ?string $prenomDeclarant = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    #[Email(mode : Email::VALIDATION_MODE_STRICT, message: 'Veuillez renseigner un email valide.', )]
    #[Assert\NotBlank(message: 'Veuillez renseigner votre e-mail.', groups: ['front_add_signalement_transport', 'front_add_signalement_erp'])]
    private ?string $emailDeclarant = null;

    public function __construct()
    {
        $this->uuid = Uuid::v4();
        $this->interventions = new ArrayCollection();
        $this->messagesThread = new ArrayCollection();
    }

    #[Assert\Callback(groups: ['front_add_signalement_transport'])]
    public function validate(ExecutionContextInterface $context, $payload): void
    {
        if (!$this->transportLineNumber && PlaceType::TYPE_TRANSPORT_AUTRE !== $this->placeType) {
            $context->buildViolation('Veuillez renseigner le numéro de ligne.')
                ->atPath('transportLineNumber')
                ->addViolation();
        }
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

    public function setTypeLogement(?string $typeLogement): self
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

    public function setTypeIntervention(?string $typeIntervention): self
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

    public function getSuperficie(): ?int
    {
        return $this->superficie;
    }

    public function setSuperficie(int $superficie): self
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

    /**
     * @return Collection<int, Intervention>
     */
    public function getInterventions(): Collection
    {
        return $this->interventions;
    }

    public function addIntervention(Intervention $intervention): self
    {
        if (!$this->interventions->contains($intervention)) {
            $this->interventions->add($intervention);
            $intervention->setSignalement($this);
        }

        return $this;
    }

    public function removeIntervention(Intervention $intervention): self
    {
        if ($this->interventions->removeElement($intervention)) {
            // set the owning side to null (unless already changed)
            if ($intervention->getSignalement() === $this) {
                $intervention->setSignalement(null);
            }
        }

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

    public function getReminderAutotraitementAt(): ?\DateTimeImmutable
    {
        return $this->reminderAutotraitementAt;
    }

    public function setReminderAutotraitementAt(?\DateTimeImmutable $reminderAutotraitementAt): self
    {
        $this->reminderAutotraitementAt = $reminderAutotraitementAt;

        return $this;
    }

    public function getResolvedAt(): ?\DateTimeImmutable
    {
        return $this->resolvedAt;
    }

    public function setResolvedAt(?\DateTimeImmutable $resolvedAt): self
    {
        $this->resolvedAt = $resolvedAt;

        return $this;
    }

    public function getSwitchedTraitementAt(): ?\DateTimeImmutable
    {
        return $this->switchedTraitementAt;
    }

    public function setSwitchedTraitementAt(?\DateTimeImmutable $switchedTraitementAt): self
    {
        $this->switchedTraitementAt = $switchedTraitementAt;

        return $this;
    }

    public function getClosedAt(): ?\DateTimeImmutable
    {
        return $this->closedAt;
    }

    public function setClosedAt(?\DateTimeImmutable $closedAt): self
    {
        $this->closedAt = $closedAt;

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
            $messagesThread->setSignalement($this);
        }

        return $this;
    }

    public function removeMessagesThread(MessageThread $messagesThread): self
    {
        if ($this->messagesThread->removeElement($messagesThread)) {
            // set the owning side to null (unless already changed)
            if ($messagesThread->getSignalement() === $this) {
                $messagesThread->setSignalement(null);
            }
        }

        return $this;
    }

    public function getUuidPublic(): ?string
    {
        return $this->uuidPublic;
    }

    public function updateUuidPublic(): self
    {
        $this->uuidPublic = uniqid();

        return $this;
    }

    public function setUuidPublic(?string $uuidPublic): self
    {
        $this->uuidPublic = $uuidPublic;

        return $this;
    }

    public function getGeoloc(): ?array
    {
        return $this->geoloc;
    }

    public function setGeoloc(array $geoloc): self
    {
        $this->geoloc = $geoloc;

        return $this;
    }

    public function setCreatedAt(\DateTimeImmutable $date): self
    {
        $this->createdAt = $date;

        return $this;
    }

    public function getType(): ?SignalementType
    {
        return $this->type;
    }

    public function setType(?SignalementType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPunaisesViewedAt(): ?\DateTimeImmutable
    {
        return $this->punaisesViewedAt;
    }

    public function setPunaisesViewedAt(?\DateTimeImmutable $punaisesViewedAt): self
    {
        $this->punaisesViewedAt = $punaisesViewedAt;

        return $this;
    }

    public function getPlaceType(): ?PlaceType
    {
        return $this->placeType;
    }

    public function setPlaceType(?PlaceType $placeType): self
    {
        $this->placeType = $placeType;

        return $this;
    }

    public function getIsPlaceAvertie(): ?bool
    {
        return $this->isPlaceAvertie;
    }

    public function setIsPlaceAvertie(?bool $isPlaceAvertie): self
    {
        $this->isPlaceAvertie = $isPlaceAvertie;

        return $this;
    }

    public function getAutresInformations(): ?string
    {
        return $this->autresInformations;
    }

    public function setAutresInformations(?string $autresInformations): self
    {
        $this->autresInformations = $autresInformations;

        return $this;
    }

    public function getNomDeclarant(): ?string
    {
        return $this->nomDeclarant;
    }

    public function setNomDeclarant(?string $nomDeclarant): self
    {
        $this->nomDeclarant = $nomDeclarant;

        return $this;
    }

    public function getPrenomDeclarant(): ?string
    {
        return $this->prenomDeclarant;
    }

    public function setPrenomDeclarant(?string $prenomDeclarant): self
    {
        $this->prenomDeclarant = $prenomDeclarant;

        return $this;
    }

    public function getNomCompletDeclarant(): ?string
    {
        return $this->getPrenomDeclarant().' '.$this->getNomDeclarant();
    }

    public function getEmailDeclarant(): ?string
    {
        return $this->emailDeclarant;
    }

    public function setEmailDeclarant(?string $emailDeclarant): self
    {
        $this->emailDeclarant = $emailDeclarant;

        return $this;
    }

    public function getTransportLineNumber(): ?string
    {
        return $this->transportLineNumber;
    }

    public function setTransportLineNumber(?string $transportLineNumber): self
    {
        $this->transportLineNumber = $transportLineNumber;

        return $this;
    }
}
