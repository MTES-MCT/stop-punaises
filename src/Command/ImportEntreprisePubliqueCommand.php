<?php

namespace App\Command;

use App\Service\Import\CsvParser;
use App\Service\Import\EntreprisePublique\EntreprisePubliqueImportLoader;
use App\Service\Upload\UploadHandlerService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'app:import-entreprise-publique',
    description: 'Import entreprise publique from storage S3',
)]
class ImportEntreprisePubliqueCommand extends Command
{
    public function __construct(
        private CsvParser $csvParser,
        private ParameterBagInterface $parameterBag,
        private EntityManagerInterface $entityManager,
        private FilesystemOperator $fileStorage,
        private UploadHandlerService $uploadHandlerService,
        private EntreprisePubliqueImportLoader $entreprisePubliqueImportLoader,
    ) {
        parent::__construct();
    }

    /**
     * @throws FilesystemException
     * @throws NonUniqueResultException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $fromFile = 'csv/entreprises.csv';
        $toFile = $this->parameterBag->get('uploads_tmp_dir').'entreprises.csv';
        if (!$this->fileStorage->fileExists($fromFile)) {
            $io->error('CSV File does not exist');

            return Command::FAILURE;
        }

        $this->uploadHandlerService->createTmpFileFromBucket($fromFile, $toFile);

        $this->entreprisePubliqueImportLoader->load(
            $this->csvParser->parseAsDict($toFile),
            $this->csvParser->getHeaders($toFile),
            $output
        );

        $countEntreprises = $this->entreprisePubliqueImportLoader->countEntreprises();

        $io->success(sprintf('%s entreprises publiques importées', $countEntreprises));

        return Command::SUCCESS;
    }
}
