<?php

namespace App\Command;

use App\Entity\Signalement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeolocateCommand extends Command
{
    protected static $defaultName = 'app:geolocate';

    public function __construct(
        private readonly HttpClientInterface $client,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Geolocates existing addresses and updates the geolocation coordinates');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $signalements = $this->entityManager->getRepository(Signalement::class)->findAll();
        $countSuccess = 0;
        $countFailed = 0;

        foreach ($signalements as $signalement) {
            $address = $signalement->getAdresse();
            $postalCode = $signalement->getCodePostal();
            $city = $signalement->getVille();

            // Compose the address string to be used in the geocoding API request
            $fullAddress = '';
            if (null !== $address) {
                $fullAddress .= $address.', ';
            }
            if (null !== $postalCode) {
                $fullAddress .= $postalCode.', ';
            }
            if (null !== $city) {
                $fullAddress .= $city;
            }

            $statusCode = Response::HTTP_SERVICE_UNAVAILABLE;
            try {
                // Make the API request to geocode the address
                $response = $this->client->request('GET', 'https://api-adresse.data.gouv.fr/search', [
                    'query' => [
                        'q' => $fullAddress,
                        'limit' => 1,
                    ],
                ]);

                $statusCode = $response->getStatusCode();
                if (200 === $statusCode) {
                    $data = json_decode($response->getContent(), true);

                    if (!empty($data['features'][0]['geometry']['coordinates'])) {
                        $coordinates = [
                            'lat' => (string) $data['features'][0]['geometry']['coordinates'][1],
                            'lng' => (string) $data['features'][0]['geometry']['coordinates'][0],
                        ];

                        // Update the geolocation coordinates in the signalement entity
                        $signalement->setGeoloc($coordinates);

                        $this->entityManager->persist($signalement);
                        $this->entityManager->flush();
                        ++$countSuccess;
                        $output->writeln('Geolocation updated for Signalement ID: '.$signalement->getId());
                    } else {
                        ++$countFailed;
                        $output->writeln('Geolocation not found for Signalement ID: '.$signalement->getId());
                    }
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
