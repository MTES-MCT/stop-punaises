<?php

namespace App\Tests\Functional\Controller\Security;

use App\Repository\UserRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\RouterInterface;

class SecurityControllerTest extends WebTestCase
{
    private ?KernelBrowser $client = null;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
    }

    #[DataProvider('provideUsers')]
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

    public static function provideUsers(): \Generator
    {
        yield 'Admin can login as Admin' => ['admin@punaises.fr', '/bo'];
        yield 'Company 1 can login as Entreprise' => ['company-01@punaises.fr', '/bo'];
        yield 'Company 3 cannot login as Entreprise' => ['company-03@punaises.fr', '/login'];
        yield 'Company 69-01 can login as Entreprise' => ['company-69-01@punaises.fr', '/bo'];
    }

    public function testShowUploadedFileWithoutSignatureIsDenied(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'company-01@punaises.fr']);
        $this->client->loginUser($user);

        /** @var RouterInterface $router */
        $router = static::getContainer()->get(RouterInterface::class);
        $route = $router->generate('show_uploaded_file', ['filename' => 'photo-abc123.jpg']);

        $this->client->request('GET', $route);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testShowUploadedFileWithTamperedSignatureIsDenied(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'company-01@punaises.fr']);
        $this->client->loginUser($user);

        /** @var UriSigner $uriSigner */
        $uriSigner = static::getContainer()->get(UriSigner::class);
        /** @var RouterInterface $router */
        $router = static::getContainer()->get(RouterInterface::class);
        $route = $router->generate('show_uploaded_file', ['filename' => 'photo-abc123.jpg'], RouterInterface::ABSOLUTE_URL);
        $signedRoute = $uriSigner->sign($route);
        $tamperedRoute = str_replace('photo-abc123.jpg', 'photo-other456.jpg', $signedRoute);

        $this->client->request('GET', $tamperedRoute);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testShowUploadedFileWithPathTraversalFilenameIsRejected(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'company-01@punaises.fr']);
        $this->client->loginUser($user);

        $this->client->request('GET', '/_up/..%2f..%2f..%2fetc%2fpasswd');

        $this->assertContains($this->client->getResponse()->getStatusCode(), [403, 404]);
    }

    public function testShowUploadedFileRequiresAuthentication(): void
    {
        /** @var UriSigner $uriSigner */
        $uriSigner = static::getContainer()->get(UriSigner::class);
        /** @var RouterInterface $router */
        $router = static::getContainer()->get(RouterInterface::class);
        $route = $router->generate('show_uploaded_file', ['filename' => 'photo-abc123.jpg'], RouterInterface::ABSOLUTE_URL);
        $signedRoute = $uriSigner->sign($route);

        $this->client->request('GET', $signedRoute);

        $this->assertResponseRedirects('/login');
    }
}
