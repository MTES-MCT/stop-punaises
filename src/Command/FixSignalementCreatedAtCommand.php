<?php

namespace App\Command;

use App\Entity\Entreprise;
use App\Entity\Enum\Declarant;
use App\Manager\SignalementManager;
use App\Repository\SignalementRepository;
use App\Service\Import\CsvParser;
use App\Service\Import\Signalement\SignalementImportMapper;
use App\Service\Upload\UploadHandlerService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'app:fix-signalement-created-at',
    description: 'Fix created at on signalement imported on storage S3',
)]
class FixSignalementCreatedAtCommand extends Command
{
    private const FLUSH_COUNT = 200;

    public function __construct(
        private CsvParser $csvParser,
        private ParameterBagInterface $parameterBag,
        private EntityManagerInterface $entityManager,
        private FilesystemOperator $fileStorage,
        private UploadHandlerService $uploadHandlerService,
        private SignalementManager $signalementManager,
        private SignalementImportMapper $signalementImportMapper,
        private SignalementRepository $signalementRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('entreprise_uuid', InputArgument::OPTIONAL, 'Entreprise uuid to target');
    }

    /**
     * @throws FilesystemException
     * @throws NonUniqueResultException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $fromFile = 'csv/signalements.csv';
        $toFile = $this->parameterBag->get('uploads_tmp_dir').'signalements.csv';
        if (!$this->fileStorage->fileExists($fromFile)) {
            $io->error('CSV File does not exist');

            return Command::FAILURE;
        }

        $this->uploadHandlerService->createTmpFileFromBucket($fromFile, $toFile);

        $data = $this->csvParser->parseAsDict($toFile);
        $headers = $this->csvParser->getHeaders($toFile);

        $countSignalementFixed = 0;
        $countSignalementNotFixed = 0;
        $countSignalementNotFound = 0;
        $progressBar = new ProgressBar($output);
        $progressBar->start(\count($data));

        // avoids loading same entreprise entity multiple times
        /** @var Entreprise $currentEntreprise */
        $currentEntreprise = null;

        foreach ($data as $item) {
            $dataMapped = $this->signalementImportMapper->map($headers, $item);
            if (!empty($dataMapped)) {
                $progressBar->advance();

                if (empty($currentEntreprise) || $currentEntreprise->getUuid() !== $dataMapped['entrepriseUUID']) {
                    $currentEntreprise = $this->entityManager->getRepository(Entreprise::class)->findOneBy(['uuid' => $dataMapped['entrepriseUUID']]);
                }
                if (!empty($currentEntreprise)) {
                    $dataMapped['entreprise'] = $currentEntreprise;
                }

                $dataMapped['declarant'] = Declarant::DECLARANT_ENTREPRISE;
                $dateIntervention = $dataMapped['dateIntervention'];
                $dateCreation = $dataMapped['createdAt'];
                if (null !== $dateIntervention) {
                    $dateCreation = $dateCreation ?? \DateTimeImmutable::createFromInterface($dateIntervention)->modify('-3 months');
                }
                $signalement = $this->signalementRepository->findOneBy([
                    'entreprise' => $dataMapped['entreprise'],
                    'declarant' => $dataMapped['declarant'],
                    'adresse' => $dataMapped['adresse'],
                    'ville' => $dataMapped['ville'],
                    'codePostal' => $dataMapped['codePostal'],
                    'nomOccupant' => $dataMapped['nomOccupant'],
                    'prenomOccupant' => $dataMapped['prenomOccupant'],
                ]);
                if ($signalement && null !== $dateCreation) {
                    ++$countSignalementFixed;
                    $signalement->setCreatedAt($dateCreation);
                    if (null === $signalement->getTypeLogement() && null !== $dataMapped['typeLogement']) {
                        $signalement->setTypeLogement($dataMapped['typeLogement']);
                    }
                    if (null === $signalement->getLocalisationDansImmeuble() && null !== $dataMapped['localisationDansImmeuble']) {
                        $signalement->setLocalisationDansImmeuble($dataMapped['localisationDansImmeuble']);
                    }
                    if (null === $signalement->getNiveauInfestation() && null !== $dataMapped['niveauInfestation']) {
                        $signalement->setNiveauInfestation($dataMapped['niveauInfestation']);
                    }
                    if (null === $signalement->getDateVisitePostTraitement() && null !== $dataMapped['dateVisitePostTraitement']) {
                        $signalement->setDateVisitePostTraitement($dataMapped['dateVisitePostTraitement']);
                    }
                    if (null === $signalement->getTypeIntervention() && null !== $dataMapped['typeIntervention']) {
                        $signalement->setTypeIntervention($dataMapped['typeIntervention']);
                    }
                    if (null === $signalement->getTypeDiagnostic() && null !== $dataMapped['typeDiagnostic']) {
                        $signalement->setTypeDiagnostic($dataMapped['typeDiagnostic']);
                    }
                    $this->signalementManager->save($signalement);

                    if (0 === $countSignalementFixed % self::FLUSH_COUNT) {
                        $this->signalementManager->flush();
                    } else {
                        $this->signalementManager->persist($signalement);
                        unset($signalement);
                    }
                } else {
                    if (null === $signalement) {
                        ++$countSignalementNotFound;
                    }
                    if (null === $dateCreation) {
                        ++$countSignalementNotFixed;
                    }
                }
            }
        }

        $this->signalementManager->flush();
        $progressBar->finish();

        $io->success(\sprintf('%s signalement(s) have been fixed', $countSignalementFixed));

        $io->success(\sprintf(
            '%s signalement(s) have been fixed, %s signalement(s) have not been found, %s signalement(s) have NOT been fixed',
            $countSignalementFixed,
            $countSignalementNotFound,
            $countSignalementNotFixed
        ));

        return Command::SUCCESS;
    }
}
