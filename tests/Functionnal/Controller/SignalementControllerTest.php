<?php

namespace App\Tests\Functionnal\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

class SignalementControllerTest extends WebTestCase
{
    public function testAddSignalementHistoriqueLogementAsEntreprise(): void
    {
        $client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);

        $user = $userRepository->findOneBy(['email' => 'company-01@punaises.fr']);

        $client->loginUser($user);

        $payloadSignalement = [
            'signalement' => [
                    '_token' => $this->generateCsrfToken($this->client, 'front-add-signalement'),
                    'adresse' => '17 Boulevard saade - quai joliette',
                    'codePostal' => '13002',
                    'codeInsee' => '13202',
                    'ville' => 'Marseille',
                    'geoloc' => '43.301787|5.364626',
                    'typeLogement' => 'appartement',
                    'localisationDansImmeuble' => 'logement',
                    'construitAvant1948' => '0',
                    'nomOccupant' => 'AHAMADA',
                    'prenomOccupant' => 'Techmind Consulting',
                    'telephoneOccupant' => '',
                    'emailOccupant' => '',
                    'typeIntervention' => 'diagnostic',
                    'dateIntervention' => '2020-10-10',
                    'agent' => '1',
                    'niveauInfestation' => '4',
                    'nomBiocide' => '',
                    'typeDiagnostic' => 'canin',
                    'nombrePiecesTraitees' => '',
                    'delaiEntreInterventions' => '',
                    'dateVisitePostTraitement' => '',
                    'prixFactureHT' => '555',
            ],
        ];

        /** @var RouterInterface $router */
        $router = self::getContainer()->get(RouterInterface::class);
        $routePostSignalement = $router->generate('app_signalement_create');
        $this->client->request('POST', $routePostSignalement, $payloadSignalement);

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $bodyContent = $this->client->getResponse()->getContent();
        $this->assertEquals(json_decode($bodyContent, true)['response'], 'success');
    }
}
