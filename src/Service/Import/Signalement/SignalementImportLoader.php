<?php

namespace App\Service\Import\Signalement;

use App\Entity\Entreprise;
use App\Entity\Enum\Declarant;
use App\Manager\SignalementManager;
use App\Repository\SignalementRepository;
use App\Repository\TerritoireRepository;
use App\Service\Signalement\GeolocateService;
use App\Service\Signalement\ZipCodeService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class SignalementImportLoader
{
    private const FLUSH_COUNT = 200;

    private array $metadata = [
        'count_signalement' => 0,
    ];

    public function __construct(
        private SignalementImportMapper $signalementImportMapper,
        private SignalementManager $signalementManager,
        private SignalementRepository $signalementRepository,
        private EntityManagerInterface $entityManager,
        private ParameterBagInterface $parameterBag,
        private LoggerInterface $logger,
        private ZipCodeService $zipCodeService,
        private TerritoireRepository $territoireRepository,
        private GeolocateService $geolocateService,
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws \Exception
     */
    public function load(Entreprise $entreprise, array $data, array $headers, ?OutputInterface $output = null): void
    {
        $countSignalement = 0;
        if ($output) {
            $progressBar = new ProgressBar($output);
            $progressBar->start(\count($data));
        }

        $lastReference = $this->signalementRepository->findLastReference();
        list($yearRef, $idRef) = explode('-', $lastReference['reference']);

        foreach ($data as $item) {
            $dataMapped = $this->signalementImportMapper->map($headers, $item);
            // TODO : check if no errors before creating signalements
            if (!empty($dataMapped)) {
                ++$countSignalement;
                $idRef = (int) $idRef + 1;
                if ($output) {
                    $progressBar->advance();
                }
                $dataMapped['entreprise'] = $entreprise;
                $dataMapped['reference'] = $yearRef.'-'.$idRef;
                $dataMapped['declarant'] = Declarant::DECLARANT_ENTREPRISE;

                $signalement = $this->signalementManager->createOrUpdate($dataMapped, true);
                $dateIntervention = $signalement->getDateIntervention();
                if (null !== $dateIntervention) {
                    $signalement->setCustomCreatedAt(\DateTimeImmutable::createFromInterface($dateIntervention)->modify('-3 months'));
                    $signalement->setClosedAt($dateIntervention);
                }

                $zipCode = $this->zipCodeService->getByCodePostal($signalement->getCodePostal());
                $territoire = $this->territoireRepository->findOneBy(['zip' => $zipCode]);
                $signalement->setTerritoire($territoire);

                $this->signalementManager->save($signalement);

                $this->geolocateService->geolocate($signalement);
                $this->metadata['count_signalement'] = $countSignalement;
                if (0 === $countSignalement % self::FLUSH_COUNT) {
                    $this->logger->info(sprintf('in progress - %s signalements saved', $countSignalement));
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
