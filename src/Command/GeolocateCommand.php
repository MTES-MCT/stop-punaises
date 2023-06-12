<?php

namespace App\Command;

use App\Entity\Signalement;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GeolocateCommand extends Command
{
    protected static $defaultName = 'app:geolocate';

    private EntityManagerInterface $entityManager;
    private Client $httpClient;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->httpClient = new Client();
    }

    protected function configure()
    {
        $this->setDescription('Geolocates existing addresses and updates the geolocation coordinates');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $signalements = $this->entityManager->getRepository(Signalement::class)->findAll();

        foreach ($signalements as $signalement) {
            $address = $signalement->getAdresse();
            $postalCode = $signalement->getCodePostal();
            $city = $signalement->getVille();

            // Compose the address string to be used in the geocoding API request
            $fullAddress = $address.', '.$postalCode.' '.$city;

            // Make the API request to geocode the address
            $response = $this->httpClient->request('GET', 'https://api-adresse.data.gouv.fr/search', [
                'query' => [
                    'q' => $fullAddress,
                    'limit' => 1,
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            if (!empty($data['features'][0]['geometry']['coordinates'])) {
                $coordinates = [
                    'lat' => (string) ($data['features'][0]['geometry']['coordinates'][1]),
                    'lng' => (string) ($data['features'][0]['geometry']['coordinates'][0]),
                ];

                // Update the geolocation coordinates in the signalement entity
                $signalement->setGeoloc($coordinates);
                // $signalement->setGeoloc(json_encode($coordinates));

                $this->entityManager->persist($signalement);
                $this->entityManager->flush();

                $output->writeln('Geolocation updated for Signalement ID: '.$signalement->getId());
            } else {
                $output->writeln('Geolocation not found for Signalement ID: '.$signalement->getId());
            }
        }

        $output->writeln('Geolocation process completed.');

        return Command::SUCCESS;
    }
}
