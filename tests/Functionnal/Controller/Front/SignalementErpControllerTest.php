<?php

namespace App\Tests\Functionnal\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

class SignalementErpControllerTest extends WebTestCase
{
    public function testPostValidFormErp(): void
    {
        $client = static::createClient();

        /** @var RouterInterface $router */
        $router = static::getContainer()->get(RouterInterface::class);
        $route = $router->generate('app_signalement_erp');
        $crawler = $client->request('GET', $route);
        $form = $crawler->selectButton('Signaler mon problème')->form();
        $form['signalement_front[punaisesViewedAt]'] = '2023-10-10';
        $form['signalement_front[punaisesViewedTimeAt]'] = '10:15';
        $form['signalement_front[nomProprietaire]'] = 'Monsieur patate';
        $form['signalement_front[adresse]'] = 'Rue de la Pata';
        $form['signalement_front[codePostal]'] = '69380';
        $form['signalement_front[ville]'] = "Chazay-d'Azergues";
        $form['signalement_front[geoloc]'] = '45.883415|4.709895';
        $form['signalement_front[codeInsee]'] = '69052';
        $form['signalement_front[placeType]'] = 'TYPE_ERP_PUBLIC';
        $form['signalement_front[isPlaceAvertie]'] = '1';
        $form['signalement_front[autresInformations]'] = "Assis paisiblement en train d'attendre mon plat de patates";
        $form['signalement_front[nomDeclarant]'] = 'Doe';
        $form['signalement_front[prenomDeclarant]'] = 'John';
        $form['signalement_front[emailDeclarant]'] = 'john.doe@punaises.com';

        $client->submit($form);
        $this->assertResponseIsSuccessful();
        $this->assertEmailCount(1);
    }

    public function testPostInvalidFormErp(): void
    {
        $client = static::createClient();

        /** @var RouterInterface $router */
        $router = static::getContainer()->get(RouterInterface::class);
        $route = $router->generate('app_signalement_erp');
        $crawler = $client->request('GET', $route);
        $form = $crawler->selectButton('Signaler mon problème')->form();
        $form['signalement_front[punaisesViewedAt]'] = '2023-10-10';
        $form['signalement_front[punaisesViewedTimeAt]'] = '10:15';
        $form['signalement_front[nomProprietaire]'] = 'Monsieur patate';
        $form['signalement_front[adresse]'] = 'Rue de la Pata';
        $form['signalement_front[codePostal]'] = '69380';
        $form['signalement_front[ville]'] = "Chazay-d'Azergues";
        $form['signalement_front[geoloc]'] = '45.883415|4.709895';
        $form['signalement_front[codeInsee]'] = '69052';
        $form['signalement_front[placeType]'] = 'TYPE_ERP_PUBLIC';
        $form['signalement_front[isPlaceAvertie]'] = '1';

        $client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertEquals(
            [
                'error' => [
                    'signalement_front[nomDeclarant]' => 'Veuillez renseigner votre nom.',
                    'signalement_front[prenomDeclarant]' => 'Veuillez renseigner votre prénom.',
                    'signalement_front[emailDeclarant]' => 'Veuillez renseigner votre email.',
                ],
            ],
            json_decode($client->getResponse()->getContent(), true));
    }

    public function testPostFormErpWithDateGreaterThanToday(): void
    {
        $client = static::createClient();

        /** @var RouterInterface $router */
        $router = static::getContainer()->get(RouterInterface::class);
        $route = $router->generate('app_signalement_erp');
        $crawler = $client->request('GET', $route);
        $form = $crawler->selectButton('Signaler mon problème')->form();

        $form['signalement_front[punaisesViewedAt]'] = '2025-10-10';
        $form['signalement_front[punaisesViewedTimeAt]'] = '10:15';
        $form['signalement_front[nomProprietaire]'] = 'Monsieur patate';
        $form['signalement_front[adresse]'] = 'Rue de la Pata';
        $form['signalement_front[codePostal]'] = '69380';
        $form['signalement_front[ville]'] = "Chazay-d'Azergues";
        $form['signalement_front[geoloc]'] = '45.883415|4.709895';
        $form['signalement_front[codeInsee]'] = '69052';
        $form['signalement_front[placeType]'] = 'TYPE_ERP_PUBLIC';
        $form['signalement_front[isPlaceAvertie]'] = '1';

        $client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $this->assertArrayHasKey('signalement_front[punaisesViewedAt]',
            json_decode($client->getResponse()->getContent(), true)['error']
        );
    }

    public function testPostFormErpWithTimeGreaterThanCurrentTime(): void
    {
        $client = static::createClient();

        /** @var RouterInterface $router */
        $router = static::getContainer()->get(RouterInterface::class);
        $route = $router->generate('app_signalement_erp');
        $crawler = $client->request('GET', $route);
        $form = $crawler->selectButton('Signaler mon problème')->form();

        $dateFuture = (new \DateTimeImmutable())->modify('+5 hour');

        $form['signalement_front[punaisesViewedAt]'] = date('Y-m-d');
        $form['signalement_front[punaisesViewedTimeAt]'] = $dateFuture->format('H:i:s');
        $form['signalement_front[nomProprietaire]'] = 'Monsieur patate';
        $form['signalement_front[adresse]'] = 'Rue de la Pata';
        $form['signalement_front[codePostal]'] = '69380';
        $form['signalement_front[ville]'] = "Chazay-d'Azergues";
        $form['signalement_front[geoloc]'] = '45.883415|4.709895';
        $form['signalement_front[codeInsee]'] = '69052';
        $form['signalement_front[placeType]'] = 'TYPE_ERP_PUBLIC';
        $form['signalement_front[isPlaceAvertie]'] = '1';

        $client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertArrayHasKey('signalement_front[punaisesViewedTimeAt]',
            json_decode($client->getResponse()->getContent(), true)['error']
        );
    }
}
