<?php

namespace App\Service\Import\EntreprisePublique;

use App\Manager\EntreprisePubliqueManager;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class EntreprisePubliqueImportLoader
{
    private int $count = 0;
    private ProgressBar $progressBar;

    public function __construct(
        private EntreprisePubliqueImportMapper $entreprisePubliqueImportMapper,
        private EntreprisePubliqueManager $entreprisePubliqueManager,
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
                $dataMapped['detection_canine'] = $isDetectionCanine;
                $dataMapped['intervention'] = !$isDetectionCanine;

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
        $entreprisePublique = $this->entreprisePubliqueManager->createOrUpdate($dataMapped);
        $this->entreprisePubliqueManager->save($entreprisePublique);
    }

    public function countEntreprises(): int
    {
        return $this->count;
    }
}
