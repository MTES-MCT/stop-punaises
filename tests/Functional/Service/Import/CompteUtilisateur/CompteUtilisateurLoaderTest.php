<?php

namespace App\Tests\Functional\Service\Import\CompteUtilisateur;

use App\Entity\Entreprise;
use App\Factory\EntrepriseFactory;
use App\Manager\EntrepriseManager;
use App\Manager\UserManager;
use App\Service\Import\CompteUtilisateur\CompteUtilisateurLoader;
use App\Service\Import\CompteUtilisateur\CompteUtilisateurMapper;
use Faker\Factory;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CompteUtilisateurLoaderTest extends KernelTestCase
{
    private CompteUtilisateurMapper $compteUtilisateurMapper;
    private EntrepriseFactory $entrepriseFactory;
    private EntrepriseManager $entrepriseManager;
    private LoggerInterface $logger;
    private ValidatorInterface $validator;
    private UserManager $userManager;

    protected function setUp(): void
    {
        $this->compteUtilisateurMapper = self::getContainer()->get(CompteUtilisateurMapper::class);
        $this->entrepriseFactory = self::getContainer()->get(EntrepriseFactory::class);
        $this->entrepriseManager = self::getContainer()->get(EntrepriseManager::class);
        $this->logger = self::getContainer()->get(LoggerInterface::class);
        $this->validator = self::getContainer()->get(ValidatorInterface::class);
        $this->userManager = self::getContainer()->get(UserManager::class);
    }

    public function testLoad(): void
    {
        $compteUtilisateurLoader = new CompteUtilisateurLoader(
            $this->compteUtilisateurMapper,
            $this->entrepriseFactory,
            $this->entrepriseManager,
            $this->validator,
            $this->logger,
            $this->userManager,
        );

        $compteUtilisateurLoader->load($this->getData(), [
            CompteUtilisateurMapper::COLUMN_ENTREPRISE,
            CompteUtilisateurMapper::COLUMN_TELEPHONE,
            CompteUtilisateurMapper::COLUMN_EMAIL_ADRESSE,
            CompteUtilisateurMapper::COLUMN_SIRET,
            CompteUtilisateurMapper::COLUMN_DEPARTEMENT_ACTIVITE,
            CompteUtilisateurMapper::COLUMN_LABEL,
        ]);

        $metadata = $compteUtilisateurLoader->getMetadata();
        $this->assertEquals(20, $metadata['nb_users_created']);
        $this->assertEquals(0, $metadata['nb_users_updated']);
        $this->assertNull($metadata['errors']);
        $this->assertContainsOnlyInstancesOf(Entreprise::class, $metadata['account_exists']);
    }

    public function getData(): array
    {
        $faker = Factory::create('fr_FR');
        $dataList = [];
        for ($i = 0; $i < 20; ++$i) {
            $dataItem = [
                CompteUtilisateurMapper::COLUMN_ENTREPRISE => $faker->company(),
                CompteUtilisateurMapper::COLUMN_TELEPHONE => '0607080910',
                CompteUtilisateurMapper::COLUMN_EMAIL_ADRESSE => $faker->email(),
                CompteUtilisateurMapper::COLUMN_SIRET => '000000000'.$i,
                CompteUtilisateurMapper::COLUMN_DEPARTEMENT_ACTIVITE => '13, 69',
                CompteUtilisateurMapper::COLUMN_LABEL => '0000'.$i,
            ];
            $dataList[] = $dataItem;
        }

        return $dataList;
    }
}
