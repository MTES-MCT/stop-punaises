<?php

namespace App\Command;

use App\Entity\Entreprise;
use App\Service\Import\CsvParser;
use App\Service\Import\Signalement\SignalementImportLoader;
use App\Service\Upload\UploadHandlerService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'app:import-signalement',
    description: 'Import signalement on storage S3',
)]
class ImportSignalementCommand extends Command
{
    public function __construct(
        private CsvParser $csvParser,
        private ParameterBagInterface $parameterBag,
        private EntityManagerInterface $entityManager,
        private FilesystemOperator $fileStorage,
        private UploadHandlerService $uploadHandlerService,
        private SignalementImportLoader $signalementImportLoader,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('entreprise_uuid', InputArgument::REQUIRED, 'Entreprise uuid to target');
    }

    /**
     * @throws FilesystemException
     * @throws NonUniqueResultException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $entrepriseUuid = $input->getArgument('entreprise_uuid');
        $entreprise = $this->entityManager->getRepository(Entreprise::class)->findOneBy(['uuid' => $entrepriseUuid]);
        if (null === $entreprise) {
            $io->error('Entreprise does not exist');

            return Command::FAILURE;
        }

        $fromFile = 'csv/signalements_'.$entrepriseUuid.'.csv';
        $toFile = $this->parameterBag->get('uploads_tmp_dir').'signalements.csv';
        if (!$this->fileStorage->fileExists($fromFile)) {
            $io->error('CSV File does not exist');

            return Command::FAILURE;
        }

        $this->uploadHandlerService->createTmpFileFromBucket($fromFile, $toFile);

        $this->signalementImportLoader->load(
            $entreprise,
            $this->csvParser->parseAsDict($toFile),
            $this->csvParser->getHeaders($toFile),
            $output
        );

        $metadata = $this->signalementImportLoader->getMetadata();

        $io->success(sprintf('%s signalement(s) have been imported', $metadata['count_signalement']));

        return Command::SUCCESS;
    }
}
