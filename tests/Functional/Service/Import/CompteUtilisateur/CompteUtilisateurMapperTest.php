<?php

namespace App\Tests\Functional\Service\Import\CompteUtilisateur;

use App\Service\Import\CompteUtilisateur\CompteUtilisateurMapper;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CompteUtilisateurMapperTest extends KernelTestCase
{
    public function testMap(): void
    {
        $faker = Factory::create('fr_FR');
        $compteUtilisateurMapper = new CompteUtilisateurMapper();
        $dataMapped = $compteUtilisateurMapper->map([
            CompteUtilisateurMapper::COLUMN_ENTREPRISE,
            CompteUtilisateurMapper::COLUMN_TELEPHONE,
            CompteUtilisateurMapper::COLUMN_EMAIL_ADRESSE,
            CompteUtilisateurMapper::COLUMN_SIRET,
            CompteUtilisateurMapper::COLUMN_DEPARTEMENT_ACTIVITE,
            CompteUtilisateurMapper::COLUMN_LABEL,
        ], [
            CompteUtilisateurMapper::COLUMN_ENTREPRISE => $faker->company(),
            CompteUtilisateurMapper::COLUMN_TELEPHONE => $faker->phoneNumber(),
            CompteUtilisateurMapper::COLUMN_EMAIL_ADRESSE => $faker->email(),
            CompteUtilisateurMapper::COLUMN_SIRET => '000000000',
            CompteUtilisateurMapper::COLUMN_DEPARTEMENT_ACTIVITE => '13, 69',
            CompteUtilisateurMapper::COLUMN_LABEL => '0000',
        ]);

        $this->assertArrayHasKey('entreprise_nom', $dataMapped);
        $this->assertArrayHasKey('entreprise_telephone', $dataMapped);
        $this->assertArrayHasKey('entreprise_email', $dataMapped);
        $this->assertArrayHasKey('entreprise_numero_siret', $dataMapped);
        $this->assertArrayHasKey('entreprise_territoires_zip', $dataMapped);
        $this->assertArrayHasKey('entreprise_numero_label', $dataMapped);

        $this->assertCount(2, $dataMapped['entreprise_territoires_zip']);
    }
}
