<?php

namespace App\Tests\Functionnal\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

class SignalementTransportControllerTest extends WebTestCase
{
    public function testPostValidFormErp(): void
    {
        $client = static::createClient();

        /** @var RouterInterface $router */
        $router = static::getContainer()->get(RouterInterface::class);
        $route = $router->generate('app_signalement_transport');
        $crawler = $client->request('GET', $route);
        $form = $crawler->selectButton('Signaler mon problème')->form();

        $form['signalement_transport[punaisesViewedAt]'] = '2023-11-10';
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

    public function testPostInvalidFormErp(): void
    {
        $client = static::createClient();

        /** @var RouterInterface $router */
        $router = static::getContainer()->get(RouterInterface::class);
        $route = $router->generate('app_signalement_transport');
        $crawler = $client->request('GET', $route);
        $form = $crawler->selectButton('Signaler mon problème')->form();
        $form['signalement_transport[punaisesViewedAt]'] = '2023-11-10';
        $form['signalement_transport[punaisesViewedTimeAt]'] = '10:15';
        $form['signalement_transport[ville]'] = 'Marseille';
        $form['signalement_transport[codePostal]'] = '13002';
        $form['signalement_transport[geoloc]'] = '45.883415|4.709895';
        $form['signalement_transport[codeInsee]'] = '13102';
        $form['signalement_transport[placeType]'] = 'TYPE_TRANSPORT_METRO';
        $form['signalement_transport[transportLineNumber]'] = 'M2';
        $form['signalement_transport[isPlaceAvertie]'] = '1';

        $client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertEquals(
            [
                'error' => [
                    'signalement_transport[nomDeclarant]',
                    'signalement_transport[prenomDeclarant]',
                    'signalement_transport[emailDeclarant]',
                ],
            ],
            json_decode($client->getResponse()->getContent(), true));
    }
}
