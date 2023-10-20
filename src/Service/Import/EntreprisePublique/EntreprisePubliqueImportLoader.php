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
    private ProgressBar $progressBar;

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
    public function load(array $data, array $headers, ?OutputInterface $output = null, bool $isDetectionCanine = false): void
    {
        $this->count = 0;
        if ($output) {
            $this->progressBar = new ProgressBar($output);
            $this->progressBar->start(\count($data));
        }

        foreach ($data as $item) {
            $dataMapped = $this->entreprisePubliqueImportMapper->map($headers, $item);
            if (!empty($dataMapped)) {
                if ($isDetectionCanine) {
                    $dataMapped['detection_canine'] = true;
                    $dataMapped['intervention'] = false;
                } else {
                    $dataMapped['detection_canine'] = false;
                    $dataMapped['intervention'] = true;
                }

                if (null !== $dataMapped['zips']) {
                    $zips = explode('|', $dataMapped['zips']);
                    foreach ($zips as $zip) {
                        $dataMapped['zip'] = $zip;
                        $this->createAndSaveEntreprise($dataMapped, $output);
                    }
                } else {
                    $this->createAndSaveEntreprise($dataMapped, $output);
                }
            }
        }

        $this->entreprisePubliqueManager->flush();
        if ($output) {
            $this->progressBar->finish();
        }
    }

    private function createAndSaveEntreprise(array $dataMapped, ?OutputInterface $output = null)
    {
        ++$this->count;
        if ($output) {
            $this->progressBar->advance();
        }
        $entreprisePublique = $this->entreprisePubliqueManager->createOrUpdate($dataMapped, true);
        $this->entreprisePubliqueManager->save($entreprisePublique);
    }

    public function countEntreprises(): int
    {
        return $this->count;
    }
}
