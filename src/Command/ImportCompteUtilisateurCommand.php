<?php

namespace App\Command;

use App\Event\EntrepriseRegisteredEvent;
use App\Repository\TerritoireRepository;
use App\Service\Import\CompteUtilisateur\CompteUtilisateurLoader;
use App\Service\Import\CsvParser;
use App\Service\Upload\UploadHandlerService;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[AsCommand(
    name: 'app:import-compte-utilisateur',
    description: 'Importe les comptes utilisateurs entreprise depuis un fichier CSV',
)]
class ImportCompteUtilisateurCommand extends Command
{
    private const PARAM_FILE_VERSION = 'file-version';

    public function __construct(
        private TerritoireRepository $territoireRepository,
        private CompteUtilisateurLoader $compteUtilisateurLoader,
        private FilesystemOperator $fileStorage,
        private UploadHandlerService $uploadHandlerService,
        private ParameterBagInterface $parameterBag,
        private EventDispatcherInterface $eventDispatcher,
        private CsvParser $csvParser,
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->addOption(self::PARAM_FILE_VERSION, null, InputOption::VALUE_REQUIRED, 'File version');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $fromFile = 'csv/entreprises-utilisateurs.csv';
        $toFile = $this->parameterBag->get('uploads_tmp_dir').'entreprises-utilisateurs.csv';
        if (null !== $fileVersion = $input->getOption(self::PARAM_FILE_VERSION)) {
            $fromFile = 'csv/entreprises-utilisateurs-'.$fileVersion.'.csv';
            $toFile = $this->parameterBag->get('uploads_tmp_dir').'entreprises-utilisateurs-'.$fileVersion.'.csv';
        }

        if (!$this->fileStorage->fileExists($fromFile)) {
            $io->error(\sprintf('%s file does not exist', $fromFile));

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
        $errors = $metadata[CompteUtilisateurLoader::METADATA_ERRORS];
        if (null !== $errors && \count($errors) > 0) {
            $io->error('Ces entreprises n\'ont pas pu être créées');
            foreach ($errors as $error) {
                $io->warning($error);
            }
        }

        $io->success(\sprintf(' %s entreprise(s) updated.'.\PHP_EOL,
            $metadata[CompteUtilisateurLoader::METADATA_NB_USERS_UPDATED]
        ));

        $entreprises = $metadata[CompteUtilisateurLoader::METADATA_ENTREPRISE_ACCOUNT_TO_CREATE];
        if (null === $entreprises) {
            $io->warning('No account to create');

            return Command::FAILURE;
        }

        $io->success(\sprintf(' %s entreprise(s) created.'.\PHP_EOL,
            $metadata[CompteUtilisateurLoader::METADATA_NB_USERS_CREATED]
        ));

        $io->success(\sprintf(' %s entreprise(s) already exists.'.\PHP_EOL,
            $metadata[CompteUtilisateurLoader::METADATA_NB_USERS_EXISTS]
        ));

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
        $progressBar->clear();
        $io->success(\sprintf(' %s user(s) account created,', $countUser));

        return Command::SUCCESS;
    }
}
