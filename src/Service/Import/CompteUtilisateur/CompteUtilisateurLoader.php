<?php

namespace App\Service\Import\CompteUtilisateur;

use App\Factory\EntrepriseFactory;
use App\Manager\EntrepriseManager;
use App\Repository\EntrepriseRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CompteUtilisateurLoader
{
    private const FLUSH_COUNT = 250;
    public const METADATA_NB_USERS_CREATED = 'nb_users_created';
    public const METADATA_ERRORS = 'errors';
    public const METADATA_ENTREPRISE_ACCOUNT_TO_CREATE = 'account_exists';

    private array $metadata = [
        self::METADATA_NB_USERS_CREATED => 0,
        self::METADATA_ERRORS => null,
        self::METADATA_ENTREPRISE_ACCOUNT_TO_CREATE => null,
    ];

    public function __construct(
        private CompteUtilisateurMapper $compteUtilisateurMapper,
        private EntrepriseFactory $entrepriseFactory,
        private EntrepriseManager $entrepriseManager,
        private EntrepriseRepository $entrepriseRepository,
        private ValidatorInterface $validator,
        private LoggerInterface $logger,
    ) {
    }

    public function load(array $data, array $headers, ?OutputInterface $output = null): void
    {
        if ($output) {
            $progressBar = new ProgressBar($output);
            $progressBar->start(\count($data));
        }

        $countEntreprise = 0;
        $lineNumber = 2;
        foreach ($data as $item) {
            $dataMapped = $this->compteUtilisateurMapper->map($headers, $item);
            if (!empty($dataMapped)) {
                ++$countEntreprise;
                if ($output) {
                    $progressBar->advance();
                }

                $entreprise = $this->entrepriseFactory->createFromArray($dataMapped);
                $errors = $this->validator->validate($entreprise);

                if (null !== $entreprise && 0 === $errors->count()) {
                    if (0 === $countEntreprise % self::FLUSH_COUNT) {
                        $this->logger->info(sprintf('in progress - %s entreprise saved', $countEntreprise));
                        $this->entrepriseManager->flush();
                    } else {
                        ++$this->metadata[self::METADATA_NB_USERS_CREATED];
                        $this->metadata[self::METADATA_ENTREPRISE_ACCOUNT_TO_CREATE][] = $entreprise;
                        $this->entrepriseManager->persist($entreprise);
                        unset($entreprise);
                    }
                } elseif ($errors->count() > 0) {
                    $this->metadata[self::METADATA_ERRORS][] = sprintf('line %d : %s', $lineNumber, (string) $errors);
                }
            }
            ++$lineNumber;
        }
        $this->entrepriseManager->flush();
        if ($output) {
            $progressBar->finish();
        }
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }
}
