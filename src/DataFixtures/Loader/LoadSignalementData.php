<?php

namespace App\DataFixtures\Loader;

use App\Entity\Enum\Declarant;
use App\Entity\Signalement;
use App\Repository\EmployeRepository;
use App\Repository\EntrepriseRepository;
use App\Repository\TerritoireRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Yaml\Yaml;

class LoadSignalementData extends Fixture implements OrderedFixtureInterface
{
    public function __construct(
        private EntrepriseRepository $entrepriseRepository,
        private EmployeRepository $employeRepository,
        private TerritoireRepository $territoireRepository)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $signalementsRows = Yaml::parseFile(__DIR__.'/../Files/Signalement.yml');
        foreach ($signalementsRows['signalements'] as $row) {
            $this->loadSignalement($manager, $row);
        }
        $manager->flush();
    }

    public function loadSignalement(ObjectManager $manager, array $row)
    {
        $faker = Factory::create('fr_FR');

        $signalement = (new Signalement())
            ->setUuid($row['uuid'])
            ->setAdresse($row['adresse'])
            ->setCodePostal($row['code_postal'])
            ->setVille($row['ville'])
            ->setTypeLogement($row['type_logement'])
            ->setConstruitAvant1948($row['construit_avant1948'])
            ->setTypeIntervention($row['type_intervention'])
            ->setNomOccupant($faker->lastName())
            ->setPrenomOccupant($faker->firstName())
            ->setEmailOccupant($faker->email())
            ->setTelephoneOccupant($faker->phoneNumber())
            ->setTypeLogement($row['type_diagnostic'])
            ->setNomBiocide($faker->word())
            ->setTypeDiagnostic($row['type_diagnostic'])
            ->setNombrePiecesTraitees($faker->randomDigitNotZero())
            ->setDelaiEntreInterventions($faker->randomDigitNotZero())
            ->setPrixFactureHT($faker->randomNumber(5))
            ->setCodeInsee($row['code_insee'])
            ->setNiveauInfestation($row['niveau_infestation'])
            ->setDateIntervention(new \DateTimeImmutable())
            ->setReference($row['reference'])
            ->setDeclarant(Declarant::from($row['declarant']))
            ->setTerritoire($this->territoireRepository->findOneBy(['zip' => $row['territoire']]));

        if (!empty($row['entreprise'])) {
            $signalement->setEntreprise($this->entrepriseRepository->findOneBy(['uuid' => $row['entreprise']]));
        }
        if (!empty($row['agent'])) {
            $signalement->setAgent($this->employeRepository->findOneBy(['uuid' => $row['agent']]));
        }
        if (\array_key_exists('autotraitement', $row)) {
            $signalement->setAutotraitement(1 == $row['autotraitement']);
        }

        $manager->persist($signalement);
    }

    public function getOrder(): int
    {
        return 4;
    }
}
