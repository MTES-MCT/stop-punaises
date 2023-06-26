<?php

namespace App\Service\Signalement;

use App\Entity\Signalement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeolocateService
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private EntityManagerInterface $entityManager)
    {
    }

    public function geolocate(Signalement $signalement): string
    {
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
        // Make the API request to geocode the address
        $response = $this->client->request('GET', 'https://api-adresse.data.gouv.fr/search', [
            'query' => [
                'q' => $fullAddress,
                'limit' => 1,
            ],
        ]);
        $statusCode = $response->getStatusCode();
        if (Response::HTTP_OK === $statusCode) {
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
            } else {
                $statusCode = Response::HTTP_NO_CONTENT;
            }
        }

        return $statusCode;
    }
}
