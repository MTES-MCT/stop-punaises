<?php

namespace App\Service\Import\Signalement;

use App\Entity\Entreprise;
use App\Entity\Enum\Declarant;
use App\Entity\Enum\SignalementType;
use App\Manager\SignalementManager;
use App\Repository\TerritoireRepository;
use App\Service\Signalement\GeolocateService;
use App\Service\Signalement\ReferenceGenerator;
use App\Service\Signalement\ZipCodeProvider;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class SignalementImportLoader
{
    private const FLUSH_COUNT = 200;

    private array $metadata = [
        'count_signalement' => 0,
    ];

    public function __construct(
        private SignalementImportMapper $signalementImportMapper,
        private SignalementManager $signalementManager,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private ZipCodeProvider $zipCodeService,
        private TerritoireRepository $territoireRepository,
        private GeolocateService $geolocateService,
        private ReferenceGenerator $referenceGenerator,
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws \Exception
     */
    public function load(?Entreprise $commandEntreprise, array $data, array $headers, ?OutputInterface $output = null): void
    {
        $countSignalement = 0;
        if ($output) {
            $progressBar = new ProgressBar($output);
            $progressBar->start(\count($data));
        }

        // avoids loading same entreprise entity multiple times
        /** @var Entreprise $currentEntreprise */
        $currentEntreprise = null;

        foreach ($data as $item) {
            $dataMapped = $this->signalementImportMapper->map($headers, $item);
            // TODO : check if no errors before creating signalements
            if (!empty($dataMapped)) {
                ++$countSignalement;
                if ($output) {
                    $progressBar->advance();
                }

                if (!empty($commandEntreprise)) {
                    $dataMapped['entreprise'] = $commandEntreprise;
                } else {
                    if (empty($currentEntreprise) || $currentEntreprise->getUuid() !== $dataMapped['entrepriseUUID']) {
                        $currentEntreprise = $this->entityManager->getRepository(Entreprise::class)->findOneBy(['uuid' => $dataMapped['entrepriseUUID']]);
                    }
                    if (!empty($currentEntreprise)) {
                        $dataMapped['entreprise'] = $currentEntreprise;
                    }
                }

                $dataMapped['declarant'] = Declarant::DECLARANT_ENTREPRISE;
                $dateIntervention = $dataMapped['dateIntervention'];

                if (null !== $dateIntervention) {
                    $dateCreation = \DateTimeImmutable::createFromInterface($dateIntervention)->modify('-3 months');
                    $dataMapped['reference'] = $this->referenceGenerator->generate($dateCreation->format('Y'));
                } else {
                    $dateCreation = null;
                    $dataMapped['reference'] = $this->referenceGenerator->generate();
                }

                $signalement = $this->signalementManager->createOrUpdate($dataMapped, true);
                if (null !== $dateCreation) {
                    $signalement->setCreatedAt($dateCreation);
                }
                if (null !== $dateIntervention) {
                    $signalement->setClosedAt($dateIntervention);
                }

                $zipCode = $this->zipCodeService->getByCodePostal($signalement->getCodePostal());
                $territoire = $this->territoireRepository->findOneBy(['zip' => $zipCode]);
                $signalement->setTerritoire($territoire);
                $signalement->setType(SignalementType::TYPE_LOGEMENT);

                $this->signalementManager->save($signalement);

                $this->geolocateService->geolocate($signalement);
                $this->metadata['count_signalement'] = $countSignalement;
                if (0 === $countSignalement % self::FLUSH_COUNT) {
                    $this->logger->info(\sprintf('in progress - %s signalements saved', $countSignalement));
                    $this->signalementManager->flush();
                } else {
                    $this->signalementManager->persist($signalement);
                    unset($signalement);
                }
            }
        }

        $this->signalementManager->flush();
        if ($output) {
            $progressBar->finish();
        }
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }
}
