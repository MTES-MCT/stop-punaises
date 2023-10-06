<?php

namespace App\Service\Import\EntreprisePublique;

use App\Manager\EntreprisePubliqueManager;
use App\Repository\EntreprisePubliqueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class EntreprisePubliqueImportLoader
{
    private int $count = 0;

    public function __construct(
        private EntreprisePubliqueImportMapper $entreprisePubliqueImportMapper,
        private EntreprisePubliqueManager $entreprisePubliqueManager,
        private EntreprisePubliqueRepository $entreprisePubliqueRepository,
        private EntityManagerInterface $entityManager,
        private ParameterBagInterface $parameterBag,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws \Exception
     */
    public function load(array $data, array $headers, ?OutputInterface $output = null): void
    {
        $this->count = 0;
        if ($output) {
            $progressBar = new ProgressBar($output);
            $progressBar->start(\count($data));
        }

        foreach ($data as $item) {
            $dataMapped = $this->entreprisePubliqueImportMapper->map($headers, $item);
            if (!empty($dataMapped)) {
                ++$this->count;
                if ($output) {
                    $progressBar->advance();
                }

                $entreprisePublique = $this->entreprisePubliqueManager->createOrUpdate($dataMapped, true);
                $this->entreprisePubliqueManager->save($entreprisePublique);
            }
        }

        $this->entreprisePubliqueManager->flush();
        if ($output) {
            $progressBar->finish();
        }
    }

    public function countEntreprises(): int
    {
        return $this->count;
    }
}
