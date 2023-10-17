<?php

namespace App\Command;

use App\Entity\Signalement;
use App\Service\Signalement\GeolocateService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:geolocate',
    description: 'Geolocates existing addresses and updates the geolocation coordinates'
)]
class GeolocateCommand extends Command
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private EntityManagerInterface $entityManager,
        private GeolocateService $geolocateService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $signalements = $this->entityManager->getRepository(Signalement::class)->findAll();
        $countSuccess = 0;
        $countFailed = 0;

        foreach ($signalements as $signalement) {
            $statusCode = Response::HTTP_SERVICE_UNAVAILABLE;
            try {
                $statusCode = $this->geolocateService->geolocate($signalement);
                if (Response::HTTP_OK === $statusCode) {
                    ++$countSuccess;
                    $output->writeln('Geolocation updated for Signalement ID: '.$signalement->getId());
                } elseif (Response::HTTP_NO_CONTENT === $statusCode) {
                    ++$countFailed;
                    $output->writeln('Geolocation not found for Signalement ID: '.$signalement->getId());
                } else {
                    ++$countFailed;
                    $output->writeln('Geolocation request failed for Signalement ID: '.$signalement->getId()
                    .' statusCode = '.$statusCode);
                }
            } catch (\Throwable $exception) {
                ++$countFailed;
                $output->writeln('Exception for Signalement ID: '.$signalement->getId()
                .' statusCode = '.$statusCode
                .' message = '.$exception->getMessage());
            }
        }
        $output->writeln('Geolocation process completed.  '.$countSuccess.' signalements geolocated, '
    .$countFailed.' signalements NOT geolocated. ');

        return Command::SUCCESS;
    }
}
