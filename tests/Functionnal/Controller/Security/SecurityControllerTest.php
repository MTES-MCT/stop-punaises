<?php

namespace App\Tests\Functionnal\Controller\Security;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

class SecurityControllerTest extends WebTestCase
{
    private ?KernelBrowser $client = null;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
    }

    /**
     * @dataProvider provideUsers
     */
    public function testLogin(string $email, string $redirectUrl): void
    {
        /** @var RouterInterface $router */
        $router = static::getContainer()->get(RouterInterface::class);

        $route = $router->generate('app_login');

        $crawler = $this->client->request('GET', $route);

        $form = $crawler->selectButton('Se connecter')->form();
        $form['email'] = $email;
        $form['password'] = 'punaises';

        $this->client->submit($form);
        $this->assertResponseRedirects($redirectUrl);
    }

    public function provideUsers(): \Generator
    {
        yield 'Admin can login as Admin' => ['admin@punaises.fr', '/bo'];
        yield 'Company 1 can login as Entreprise' => ['company-01@punaises.fr', '/bo'];
        yield 'Company 3 cannot login as Entreprise' => ['company-03@punaises.fr', '/login'];
        yield 'Company 69-01 can login as Entreprise' => ['company-69-01@punaises.fr', '/bo'];
    }
}
