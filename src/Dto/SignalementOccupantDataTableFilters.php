<?php

namespace App\Dto;

class SignalementOccupantDataTableFilters
{
    public function __construct(
        private ?string $statut = null,
        private ?string $zip = null,
        private ?string $date = null,
        private ?string $niveauInfestation = null,
        private ?string $adresse = null,
        private ?string $type = null,
        private ?string $etatInfestation = null,
        private ?string $motifCloture = null,
    ) {
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function getZip(): string
    {
        return $this->zip;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function getNiveauInfestation(): string
    {
        return $this->niveauInfestation;
    }

    public function getAdresse(): string
    {
        return $this->adresse;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getEtatInfestation(): string
    {
        return $this->etatInfestation;
    }

    public function getMotifCloture(): string
    {
        return $this->motifCloture;
    }
}
