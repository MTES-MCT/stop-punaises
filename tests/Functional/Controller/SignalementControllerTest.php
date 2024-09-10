<?php

namespace App\Tests\Functional\Controller;

use App\Repository\UserRepository;
use App\Tests\SessionHelper;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

class SignalementControllerTest extends WebTestCase
{
    use SessionHelper;

    public function testAddSignalementHistoriqueLogementAsEntreprise(): void
    {
        $client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);

        $user = $userRepository->findOneBy(['email' => 'company-01@punaises.fr']);

        $client->loginUser($user);

        $payloadSignalement = [
            'signalement_history' => [
                '_token' => $this->generateCsrfToken($client, 'signalement_history'),
                'adresse' => '17 Boulevard saade - quai joliette',
                'codePostal' => '13002',
                'codeInsee' => '13202',
                'ville' => 'Marseille',
                'geoloc' => '43.301787|5.364626',
                'typeLogement' => 'appartement',
                'localisationDansImmeuble' => 'logement',
                'construitAvant1948' => '0',
                'nomOccupant' => 'Doe',
                'prenomOccupant' => 'John',
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
        $client->request('POST', $routePostSignalement, $payloadSignalement);

        $this->assertResponseRedirects('/bo/historique');
    }
}
