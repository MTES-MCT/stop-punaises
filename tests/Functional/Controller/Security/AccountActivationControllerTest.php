<?php

namespace App\Tests\Functional\Controller\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

class AccountActivationControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private User $user;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $this->user = $userRepository->findOneBy(['email' => 'company-03@punaises.fr']);
        $this->user->setToken('test-token');
        $this->user->setTokenExpiredAt((new \DateTimeImmutable())->modify('+1 hour'));
        $this->entityManager->flush();
    }

    public function testActivationUserFormSubmit(): void
    {
        /** @var RouterInterface $router */
        $router = self::getContainer()->get(RouterInterface::class);

        $route = $router->generate('activate_account', ['id' => $this->user->getId(), 'token' => $this->user->getToken()]);
        $this->client->request('GET', $route);

        $password = 'Stop-Punaise01';
        $this->client->submitForm('Confirmer', [
            'password' => $password,
            'password-repeat' => $password,
        ]);

        $this->assertResponseRedirects('/login');
    }

    public function testActivationUserFormSubmitWithMismatchedPassword(): void
    {
        /** @var RouterInterface $router */
        $router = self::getContainer()->get(RouterInterface::class);

        $route = $router->generate('activate_account', ['id' => $this->user->getId(), 'token' => $this->user->getToken()]);
        $this->client->request('GET', $route);

        $this->client->submitForm('Confirmer', [
            'password' => 'un',
            'password-repeat' => 'deux',
        ]);

        $this->assertSelectorTextContains(
            '.fr-alert.fr-alert--error.fr-alert--sm',
            'Les mots de passe ne correspondent pas.'
        );
    }

    /**
     * @dataProvider provideInvalidPassword
     */
    public function testActivationUserFormSubmitWithInvalidPassword(string $expectedResult, string $password): void
    {
        /** @var RouterInterface $router */
        $router = self::getContainer()->get(RouterInterface::class);

        $route = $router->generate('activate_account', ['id' => $this->user->getId(), 'token' => $this->user->getToken()]);
        $this->client->request('GET', $route);

        $this->client->submitForm('Confirmer', [
            'password' => $password,
            'password-repeat' => $password,
        ]);

        $this->assertSelectorTextContains(
            '.fr-alert.fr-alert--error.fr-alert--sm',
            $expectedResult
        );
    }

    public function provideInvalidPassword(): \Generator
    {
        yield 'blank' => ['Cette valeur ne doit pas être vide', ''];
        yield 'short' => ['Le mot de passe doit contenir au moins 8 caractères', 'short'];
        yield 'no_uppercase' => ['Le mot de passe doit contenir au moins une lettre majuscule', 'nouppercase'];
        yield 'no_lowercase' => ['Le mot de passe doit contenir au moins une lettre minuscule', 'NOLOWERCASE'];
        yield 'no_digit' => ['Le mot de passe doit contenir au moins un chiffre', 'NoDigitNoDigit'];
        yield 'no_special' => ['Le mot de passe doit contenir au moins un caractère spécial', 'NoSpecial'];
    }
}
