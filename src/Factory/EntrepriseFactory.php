<?php

namespace App\Factory;

use App\Entity\Entreprise;
use App\Repository\TerritoireRepository;

class EntrepriseFactory
{
    public function __construct(private TerritoireRepository $territoireRepository)
    {
    }

    public function createInstanceFrom(
        string $nom,
        string $email,
        string $numeroSiret,
        string $telephone,
        string $numeroLabel,
        array $territoiresZip,
    ): Entreprise {
        $entreprise = (new Entreprise())
            ->setNom($nom)
            ->setEmail($email)
            ->setNumeroSiret($numeroSiret)
            ->setTelephone($telephone)
            ->setNumeroLabel($numeroLabel);

        foreach ($territoiresZip as $territoireZip) {
            $territoire = $this->territoireRepository->findOneBy(['zip' => $territoireZip]);
            if (null !== $territoire) {
                $entreprise->addTerritoire($territoire);
            }
        }

        return $entreprise;
    }

    public function createFromArray(array $data): ?Entreprise
    {
        if (\count($data) < 6) {
            return null;
        }

        return $this->createInstanceFrom(
            nom: $data['entreprise_nom'] ?? null,
            email: $data['entreprise_email'] ?? null,
            numeroSiret: $data['entreprise_numero_siret'] ?? null,
            telephone: $data['entreprise_telephone'] ?? null,
            numeroLabel: $data['entreprise_numero_label'] ?? null,
            territoiresZip: $data['entreprise_territoires_zip'] ?? null
        );
    }
}
