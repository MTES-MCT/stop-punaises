<?php

namespace App\Tests\Functional\Controller\Front;

use App\Entity\Signalement;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

class SignalementTransportControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        date_default_timezone_set(Signalement::DEFAULT_TIMEZONE);
    }

    public function testPostValidFormTransport(): void
    {
        $client = static::createClient();

        /** @var RouterInterface $router */
        $router = static::getContainer()->get(RouterInterface::class);
        $route = $router->generate('app_front_signalement_transport');
        $crawler = $client->request('GET', $route);
        $form = $crawler->selectButton('Signaler mon problème')->form();

        $form['signalement_transport[punaisesViewedAt]'] = '2022-11-10';
        $form['signalement_transport[punaisesViewedTimeAt]'] = '10:15';
        $form['signalement_transport[ville]'] = 'Marseille';
        $form['signalement_transport[codePostal]'] = '13002';
        $form['signalement_transport[geoloc]'] = '45.883415|4.709895';
        $form['signalement_transport[codeInsee]'] = '13102';
        $form['signalement_transport[placeType]'] = 'TYPE_TRANSPORT_METRO';
        $form['signalement_transport[transportLineNumber]'] = 'M2';
        $form['signalement_transport[isPlaceAvertie]'] = '1';
        $form['signalement_transport[nomDeclarant]'] = 'Doe';
        $form['signalement_transport[prenomDeclarant]'] = 'John';
        $form['signalement_transport[emailDeclarant]'] = 'john.doe@punaises.com';

        $client->submit($form);
        $this->assertResponseIsSuccessful();
        $this->assertEmailCount(1);
    }

    public function testPostFormTransportAutreWithLineNumber(): void
    {
        $client = static::createClient();

        /** @var RouterInterface $router */
        $router = static::getContainer()->get(RouterInterface::class);
        $route = $router->generate('app_front_signalement_transport');
        $crawler = $client->request('GET', $route);
        $form = $crawler->selectButton('Signaler mon problème')->form();

        $form['signalement_transport[punaisesViewedAt]'] = '2022-11-10';
        $form['signalement_transport[punaisesViewedTimeAt]'] = '10:15';
        $form['signalement_transport[ville]'] = 'Marseille';
        $form['signalement_transport[codePostal]'] = '13002';
        $form['signalement_transport[geoloc]'] = '45.883415|4.709895';
        $form['signalement_transport[codeInsee]'] = '13102';
        $form['signalement_transport[placeType]'] = 'TYPE_TRANSPORT_AUTRE';
        $form['signalement_transport[isPlaceAvertie]'] = '1';
        $form['signalement_transport[nomDeclarant]'] = 'Doe';
        $form['signalement_transport[prenomDeclarant]'] = 'John';
        $form['signalement_transport[emailDeclarant]'] = 'john.doe@punaises.com';

        $client->submit($form);
        $this->assertResponseIsSuccessful();
        $this->assertEmailCount(1);
    }

    public function testPostInvalidFormTransport(): void
    {
        $client = static::createClient();

        /** @var RouterInterface $router */
        $router = static::getContainer()->get(RouterInterface::class);
        $route = $router->generate('app_front_signalement_transport');
        $crawler = $client->request('GET', $route);
        $form = $crawler->selectButton('Signaler mon problème')->form();
        $form['signalement_transport[punaisesViewedAt]'] = '2023-10-10';
        $form['signalement_transport[punaisesViewedTimeAt]'] = '10:15';
        $form['signalement_transport[ville]'] = 'Marseille';
        $form['signalement_transport[codePostal]'] = '13002';
        $form['signalement_transport[geoloc]'] = '45.883415|4.709895';
        $form['signalement_transport[codeInsee]'] = '13102';
        $form['signalement_transport[placeType]'] = 'TYPE_TRANSPORT_METRO';
        $form['signalement_transport[isPlaceAvertie]'] = '1';

        $client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertEquals(
            [
                'error' => [
                    'signalement_transport[nomDeclarant]' => 'Veuillez renseigner votre nom.',
                    'signalement_transport[prenomDeclarant]' => 'Veuillez renseigner votre prénom.',
                    'signalement_transport[emailDeclarant]' => 'Veuillez renseigner votre email.',
                    'signalement_transport[transportLineNumber]' => 'Veuillez renseigner le numéro de ligne.',
                ],
            ],
            json_decode($client->getResponse()->getContent(), true));
    }

    public function testPostFormTransportWithDateGreaterThanToday(): void
    {
        $client = static::createClient();

        /** @var RouterInterface $router */
        $router = static::getContainer()->get(RouterInterface::class);
        $route = $router->generate('app_front_signalement_transport');
        $crawler = $client->request('GET', $route);
        $form = $crawler->selectButton('Signaler mon problème')->form();

        $form['signalement_transport[punaisesViewedAt]'] = '2025-11-10';
        $form['signalement_transport[punaisesViewedTimeAt]'] = '10:15';
        $form['signalement_transport[ville]'] = 'Marseille';
        $form['signalement_transport[codePostal]'] = '13002';
        $form['signalement_transport[geoloc]'] = '45.883415|4.709895';
        $form['signalement_transport[codeInsee]'] = '13102';
        $form['signalement_transport[placeType]'] = 'TYPE_TRANSPORT_METRO';
        $form['signalement_transport[transportLineNumber]'] = 'M2';
        $form['signalement_transport[isPlaceAvertie]'] = '1';
        $form['signalement_transport[nomDeclarant]'] = 'Doe';
        $form['signalement_transport[prenomDeclarant]'] = 'John';
        $form['signalement_transport[emailDeclarant]'] = 'john.doe@punaises.com';

        $client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $this->assertArrayHasKey('signalement_transport[punaisesViewedAt]',
            json_decode($client->getResponse()->getContent(), true)['error']
        );
    }

    public function testPostFormTransportWithTimeGreaterThanCurrentTime(): void
    {
        $client = static::createClient();

        /** @var RouterInterface $router */
        $router = static::getContainer()->get(RouterInterface::class);
        $route = $router->generate('app_front_signalement_transport');
        $crawler = $client->request('GET', $route);
        $form = $crawler->selectButton('Signaler mon problème')->form();

        $dateFuture = (new \DateTimeImmutable())->modify('+5 minutes');
        $form['signalement_transport[punaisesViewedAt]'] = date('Y-m-d');
        $form['signalement_transport[punaisesViewedTimeAt]'] = $dateFuture->format('H:i:s');
        $form['signalement_transport[ville]'] = 'Marseille';
        $form['signalement_transport[codePostal]'] = '13002';
        $form['signalement_transport[geoloc]'] = '45.883415|4.709895';
        $form['signalement_transport[codeInsee]'] = '13102';
        $form['signalement_transport[placeType]'] = 'TYPE_TRANSPORT_METRO';
        $form['signalement_transport[transportLineNumber]'] = 'M2';
        $form['signalement_transport[isPlaceAvertie]'] = '1';
        $form['signalement_transport[nomDeclarant]'] = 'Doe';
        $form['signalement_transport[prenomDeclarant]'] = 'John';
        $form['signalement_transport[emailDeclarant]'] = 'john.doe@punaises.com';

        $client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertArrayHasKey('signalement_transport[punaisesViewedTimeAt]',
            json_decode($client->getResponse()->getContent(), true)['error']
        );
    }
}
