<?php

namespace App\Tests\Functional\Controller\Front;

use App\Tests\SessionHelper;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

class SignalementControllerTest extends WebTestCase
{
    use SessionHelper;

    private ?KernelBrowser $client = null;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
    }

    /** @dataProvider providePayloadSignalement */
    public function testAddSignalementLogement(array $payload, ?string $codePostal = null): void
    {
        $csrf_token = $this->generateCsrfToken($this->client, 'signalement_front');
        $payload['_token'] = $csrf_token;
        $payloadSignalement = [
            'signalement_front' => $payload,
            'code-postal' => $codePostal,
        ];

        /** @var RouterInterface $router */
        $router = self::getContainer()->get(RouterInterface::class);
        $routePostSignalement = $router->generate('app_front_signalement_add');
        $this->client->request('POST', $routePostSignalement, $payloadSignalement);

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $bodyContent = $this->client->getResponse()->getContent();
        $this->assertStringContainsString('{"response":"success"}', $bodyContent);
        $this->assertEquals(json_decode($bodyContent, true)['response'], 'success');
    }

    public function providePayloadSignalement(): \Generator
    {
        yield 'Post signalement in territory not open' => [
            [
                'superficie' => '45',
                'adresse' => '8 chemin de la route',
                'codePostal' => '18250',
                'codeInsee' => '',
                'ville' => 'Achères',
                'geoloc' => '',
                'nomProprietaire' => '',
                'numeroAllocataire' => '',
                'infestationLogementsVoisins' => '2',
                'niveauInfestation' => '0',
                'nomOccupant' => 'Doe',
                'prenomOccupant' => 'John',
                'telephoneOccupant' => '0607060706',
                'emailOccupant' => 'john.doe@punaises.com',
                'autotraitement' => 'true',
            ],
            '18250',
        ];

        yield 'Post signalement in territory open' => [
            [
                'typeLogement' => 'appartement',
                'superficie' => '34',
                'adresse' => '31 Rue des phoceens',
                'codePostal' => '91270',
                'codeInsee' => '13202',
                'ville' => 'Marseille',
                'geoloc' => '43.302225|5.367932',
                'locataire' => '1',
                'nomProprietaire' => '13 Habitat',
                'logementSocial' => '0',
                'allocataire' => '0',
                'numeroAllocataire' => '',
                'dureeInfestation' => 'MORE-6-MONTHS',
                'infestationLogementsVoisins' => '1',
                'piquresExistantes' => '1',
                'piquresConfirmees' => '1',
                'dejectionsTrouvees' => 'true',
                'dejectionsNombrePiecesConcernees' => '2',
                'dejectionsFaciliteDetections' => 'RECHERCHE',
                'dejectionsLieuxObservations' => [
                    'LIT',
                    'CANAPE',
                    'MEUBLES',
                ],
                'oeufsEtLarvesTrouves' => 'true',
                'oeufsEtLarvesNombrePiecesConcernees' => '1',
                'oeufsEtLarvesFaciliteDetections' => 'FACILE',
                'oeufsEtLarvesLieuxObservations' => [
                    'LIT',
                    'CANAPE',
                    'MEUBLES',
                    'MURS',
                ],
                'punaisesTrouvees' => 'true',
                'punaisesNombrePiecesConcernees' => '1',
                'punaisesFaciliteDetections' => 'FACILE',
                'punaisesLieuxObservations' => [
                    'LIT',
                ],
                'niveauInfestation' => '3',
                'nomOccupant' => 'Doe',
                'prenomOccupant' => 'John',
                'telephoneOccupant' => '0611451245',
                'emailOccupant' => 'john.doe@yopmail.com',
                'autotraitement' => 'true',
            ],
        ];
    }
}
