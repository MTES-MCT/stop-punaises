<?php

namespace App\DataFixtures\Loader;

use App\Entity\Entreprise;
use App\Repository\TerritoireRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Yaml\Yaml;

class LoadEntrepriseData extends Fixture implements OrderedFixtureInterface
{
    public function __construct(private TerritoireRepository $territoireRepository)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $entrepriseRows = Yaml::parseFile(__DIR__.'/../Files/Entreprise.yml');
        foreach ($entrepriseRows['entreprises'] as $row) {
            $this->loadEntreprises($manager, $row);
        }
        $manager->flush();
    }

    public function loadEntreprises(ObjectManager $manager, array $row): void
    {
        $faker = Factory::create('fr_FR');

        $entreprise = (new Entreprise())
            ->setUuid($row['uuid'])
            ->setNom($faker->company())
            ->setNumeroSiret($row['siret'])
            ->setEmail($faker->companyEmail())
            ->setTelephone($faker->phoneNumber())
            ->setNumeroLabel($faker->randomNumber(8))
            ->addTerritoire($this->territoireRepository->findOneBy(['nom' => $row['territory']]));

        $manager->persist($entreprise);
    }

    public function getOrder(): int
    {
        return 2;
    }
}
