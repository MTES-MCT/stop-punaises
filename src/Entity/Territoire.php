<?php

namespace App\Entity;

use App\Entity\Behaviour\ActivableTrait;
use App\Repository\TerritoireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TerritoireRepository::class)]
class Territoire
{
    use ActivableTrait;

    public const CORSE_DU_SUD_CODE_DEPARTMENT_2A = '2A';
    public const HAUTE_CORSE_CODE_DEPARTMENT_2B = '2B';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 3)]
    private ?string $zip = null;

    #[ORM\ManyToMany(targetEntity: Entreprise::class, mappedBy: 'territoires')]
    private Collection $entreprises;

    #[ORM\OneToMany(mappedBy: 'territoire', targetEntity: Signalement::class)]
    private Collection $signalements;

    public function __construct()
    {
        $this->entreprises = new ArrayCollection();
        $this->signalements = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->zip.' - '.$this->nom;
    }

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

    public function getNomComplet(): ?string
    {
        return $this->getZip().' - '.$this->getNom();
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

    /**
     * @return Collection<int, Entreprise>
     */
    public function getEntreprises(): Collection
    {
        return $this->entreprises;
    }

    public function addEntreprise(Entreprise $entreprise): self
    {
        if (!$this->entreprises->contains($entreprise)) {
            $this->entreprises->add($entreprise);
            $entreprise->addTerritoire($this);
        }

        return $this;
    }

    public function removeEntreprise(Entreprise $entreprise): self
    {
        if ($this->entreprises->removeElement($entreprise)) {
            $entreprise->removeTerritoire($this);
        }

        return $this;
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
            $signalement->setTerritoire($this);
        }

        return $this;
    }

    public function removeSignalement(Signalement $signalement): self
    {
        if ($this->signalements->removeElement($signalement)) {
            // set the owning side to null (unless already changed)
            if ($signalement->getTerritoire() === $this) {
                $signalement->setTerritoire(null);
            }
        }

        return $this;
    }
}
