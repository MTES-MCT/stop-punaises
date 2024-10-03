<?php

namespace App\Service\Signalement;

use App\Entity\Enum\SignalementType;
use App\Entity\Signalement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class GeolocateService
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private EntityManagerInterface $entityManager)
    {
    }

    public function geolocate(Signalement $signalement): int
    {
        if (SignalementType::TYPE_TRANSPORT !== $signalement->getType()) {
            $response = $this->geolocateAddress($signalement);
        } else {
            $response = $this->geolocateMunicipality($signalement);
        }
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

    private function geolocateAddress(Signalement $signalement): ResponseInterface
    {
        $address = $signalement->getAdresse();
        $postalCode = $signalement->getCodePostal();
        $city = $signalement->getVille();
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

        return $this->client->request('GET', 'https://api-adresse.data.gouv.fr/search', [
            'query' => [
                'q' => $fullAddress,
                'limit' => 1,
            ],
        ]);
    }

    private function geolocateMunicipality(Signalement $signalement): ResponseInterface
    {
        return $this->client->request('GET', 'https://api-adresse.data.gouv.fr/search', [
            'query' => [
                'q' => $signalement->getVille(),
                'type' => 'municipality',
                'limit' => 1,
            ],
        ]);
    }
}
