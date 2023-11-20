<?php

namespace App\Tests\Functionnal\Service\Factory;

use App\Factory\EntrepriseFactory;
use App\Repository\TerritoireRepository;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EntrepriseFactoryTest extends KernelTestCase
{
    private TerritoireRepository $territoireRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->territoireRepository = self::getContainer()->get(TerritoireRepository::class);
    }

    public function testCreateFromArrayWithSuccess(): void
    {
        $faker = Factory::create('fr_FR');
        $data = [
            'entreprise_nom' => $faker->company(),
            'entreprise_email' => $faker->companyEmail(),
            'entreprise_numero_siret' => '0000000000',
            'entreprise_telephone' => $faker->phoneNumber,
            'entreprise_numero_label' => $faker->buildingNumber(),
            'entreprise_territoires_zip' => ['13', '69'],
        ];

        $entreprise = (new EntrepriseFactory($this->territoireRepository))->createFromArray($data);

        $this->assertNotNull($entreprise->getNom());
        $this->assertNotNull($entreprise->getEmail());
        $this->assertNotNull($entreprise->getNumeroSiret());
        $this->assertNotNull($entreprise->getTelephone());
        $this->assertNotNull($entreprise->getNumeroLabel());
        $this->assertGreaterThan(1, \count($entreprise->getTerritoireIds()));
    }

    public function testCreateFromArrayWithFailure(): void
    {
        $data = [
            'entreprise_nom' => '',
        ];

        $entreprise = (new EntrepriseFactory($this->territoireRepository))->createFromArray($data);

        $this->assertNull($entreprise);
    }
}
