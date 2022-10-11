<?php

namespace App\DataFixtures\Loader;

use App\Entity\Employe;
use App\Repository\EntrepriseRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Yaml\Yaml;

class LoadEmployeData extends Fixture implements OrderedFixtureInterface
{
    public function __construct(private EntrepriseRepository $entrepriseRepository)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $entrepriseRows = Yaml::parseFile(__DIR__.'/../Files/Employe.yml');
        foreach ($entrepriseRows['employes'] as $row) {
            $this->loadEmploye($manager, $row);
        }
        $manager->flush();
    }

    public function loadEmploye(ObjectManager $manager, array $row)
    {
        $faker = Factory::create('fr_FR');

        $employe = (new Employe())
            ->setUuid($row['uuid'])
            ->setNom($faker->lastName())
            ->setPrenom($faker->firstName())
            ->setTelephone($faker->phoneNumber())
            ->setEmail($faker->email())
            ->setEntreprise($this->entrepriseRepository->findOneBy(['uuid' => $row['entreprise']]))
            ->setNumeroCertification($faker->randomNumber(8));

        $manager->persist($employe);
    }

    public function getOrder(): int
    {
        return 3;
    }
}
