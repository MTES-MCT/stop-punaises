<?php

namespace App\DataFixtures\Loader;

use App\Entity\Territoire;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

class LoadTerritoireData extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $territoiresRows = Yaml::parseFile(__DIR__.'/../Files/Territoire.yml');
        foreach ($territoiresRows['territoires'] as $row) {
            $this->loadTerritories($manager, $row);
        }
        $manager->flush();
    }

    public function loadTerritories(ObjectManager $manager, array $row): void
    {
        $territoire = (new Territoire())
            ->setZip($row['zip'])
            ->setNom($row['name'])
            ->setActive($row['active']);

        $manager->persist($territoire);
    }

    public function getOrder(): int
    {
        return 1;
    }
}
