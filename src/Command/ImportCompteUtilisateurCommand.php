<?php

namespace App\Command;

use App\Event\EntrepriseRegisteredEvent;
use App\Repository\TerritoireRepository;
use App\Service\Import\CompteUtilisateur\CompteUtilisateurLoader;
use App\Service\Import\CsvParser;
use App\Service\Upload\UploadHandlerService;
use League\Flysystem\FilesystemOperator;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'app:import-compte-utilisateur',
    description: 'Importe les comptes utilisateurs entreprise depuis un fichier CSV',
)]
class ImportCompteUtilisateurCommand extends Command
{
    public function __construct(
        private TerritoireRepository $territoireRepository,
        private CompteUtilisateurLoader $compteUtilisateurLoader,
        private FilesystemOperator $fileStorage,
        private UploadHandlerService $uploadHandlerService,
        private ParameterBagInterface $parameterBag,
        private EventDispatcherInterface $eventDispatcher,
        private CsvParser $csvParser
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fromFile = 'csv/entreprises-utilisateurs.csv';
        $toFile = $this->parameterBag->get('uploads_tmp_dir').'entreprises-utilisateurs.csv';

        if (!$this->fileStorage->fileExists($fromFile)) {
            $io->error('CSV File does not exist');

            return Command::FAILURE;
        }

        $this->uploadHandlerService->createTmpFileFromBucket($fromFile, $toFile);

        $csvParser = $this->csvParser->setDelimiter(',');
        $this->compteUtilisateurLoader->load(
            $csvParser->parseAsDict($toFile),
            $csvParser->getHeaders($toFile),
            $output
        );

        $metadata = $this->compteUtilisateurLoader->getMetadata();
        if (\count($errors = $metadata[CompteUtilisateurLoader::METADATA_ERRORS]) > 0) {
            $io->error('Ces entreprises n\'ont pas pu être créées');
            foreach ($errors as $error) {
                $io->warning($error);
            }
        }

        $entreprises = $metadata[CompteUtilisateurLoader::METADATA_ENTREPRISE_ACCOUNT_TO_CREATE];
        if (null === $entreprises) {
            $io->warning('No account to create');

            return Command::FAILURE;
        }

        $io->success(sprintf(' %s entreprise(s) created,', $metadata[CompteUtilisateurLoader::METADATA_NB_USERS_CREATED]));

        $countUser = 0;
        $countEntreprise = \count($entreprises);
        $progressBar = new ProgressBar($output);
        $progressBar->start($countEntreprise);
        foreach ($entreprises as $entreprise) {
            $this->eventDispatcher->dispatch(
                new EntrepriseRegisteredEvent($entreprise),
                EntrepriseRegisteredEvent::NAME
            );
            ++$countUser;
            $progressBar->advance();
        }
        $progressBar->finish();

        $io->success(sprintf(' %s user(s) account created,', $countUser));

        return Command::SUCCESS;
    }
}
