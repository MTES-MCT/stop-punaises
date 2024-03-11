<?php

namespace App\DataFixtures\Loader;

use App\Entity\Enum\Declarant;
use App\Entity\Enum\PlaceType;
use App\Entity\Enum\SignalementType;
use App\Entity\Signalement;
use App\Repository\EmployeRepository;
use App\Repository\EntrepriseRepository;
use App\Repository\TerritoireRepository;
use App\Service\Signalement\ReferenceGenerator;
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
        private TerritoireRepository $territoireRepository,
        private ReferenceGenerator $referenceGenerator,
    ) {
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
            ->setUuidPublic($row['uuid_public'])
            ->setCodePostal($row['code_postal'])
            ->setVille($row['ville'])
            ->setGeoloc(json_decode($row['geoloc'], true))
            ->setCodeInsee($row['code_insee'])
            ->setReference($this->referenceGenerator->generate())
            ->setDeclarant(Declarant::from($row['declarant']))
            ->setTerritoire($this->territoireRepository->findOneBy(['zip' => $row['territoire']]))
            ->setType(isset($row['type']) ? SignalementType::from($row['type']) : SignalementType::TYPE_LOGEMENT);

        if (!empty($row['entreprise'])) {
            $signalement->setEntreprise($this->entrepriseRepository->findOneBy(['uuid' => $row['entreprise']]));
        }
        if (!empty($row['agent'])) {
            $signalement->setAgent($this->employeRepository->findOneBy(['uuid' => $row['agent']]));
        }
        if (\array_key_exists('autotraitement', $row)) {
            $signalement->setAutotraitement(1 == $row['autotraitement']);
        }

        if (isset($row['type']) && ($row['type'] == SignalementType::TYPE_ERP->name || $row['type'] == SignalementType::TYPE_TRANSPORT->name)) {
            $signalement->setNomDeclarant($faker->lastName())
                ->setPrenomDeclarant($faker->firstName())
                ->setEmailDeclarant($faker->email())
                ->setPunaisesViewedAt((new \DateTimeImmutable())->modify('-1 days'));
        } else {
            $signalement->setNomOccupant($faker->lastName())
                ->setPrenomOccupant($faker->firstName())
                ->setEmailOccupant($faker->email())
                ->setTelephoneOccupant($faker->phoneNumber())
                ->setNomBiocide($faker->word())
                ->setNombrePiecesTraitees($faker->randomDigitNotZero())
                ->setDelaiEntreInterventions($faker->randomDigitNotZero())
                ->setPrixFactureHT($faker->randomNumber(5))
                ->setDateIntervention(new \DateTimeImmutable());
        }
        if (!empty($row['adresse'])) {
            $signalement->setAdresse($row['adresse']);
        }
        if (!empty($row['nom_proprietaire'])) {
            $signalement->setNomProprietaire($row['nom_proprietaire']);
        }
        if (!empty($row['place_type'])) {
            $signalement->setPlaceType(PlaceType::from($row['place_type']));
        }
        if (!empty($row['transport_line_number'])) {
            $signalement->setTransportLineNumber($row['transport_line_number']);
        }
        if (isset($row['is_place_averti'])) {
            $signalement->setIsPlaceAvertie(1 == $row['is_place_averti']);
        }
        if (!empty($row['autres_informations'])) {
            $signalement->setAutresInformations($row['autres_informations']);
        }
        if (!empty($row['type_logement'])) {
            $signalement->setTypeLogement($row['type_logement']);
        }
        if (!empty($row['construit_avant1948'])) {
            $signalement->setConstruitAvant1948($row['construit_avant1948']);
        }
        if (!empty($row['type_intervention'])) {
            $signalement->setTypeIntervention($row['type_intervention']);
        }
        if (!empty($row['type_diagnostic'])) {
            $signalement->setTypeDiagnostic($row['type_diagnostic']);
        }

        if (isset($row['niveau_infestation'])) {
            $signalement->setNiveauInfestation($row['niveau_infestation']);
        }

        $manager->persist($signalement);
        $manager->flush();
    }

    public function getOrder(): int
    {
        return 4;
    }
}
