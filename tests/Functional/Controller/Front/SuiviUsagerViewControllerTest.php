<?php

namespace App\Tests\Functional\Controller\Front;

use App\Entity\Signalement;
use App\Repository\SignalementRepository;
use App\Repository\UserRepository;
use App\Tests\SessionHelper;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class SuiviUsagerViewControllerTest extends WebTestCase
{
    use SessionHelper;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
    }

    /** @dataProvider provideRoutes */
    public function testSignalementSuccessfullyDisplay(string $route, Signalement $signalement): void
    {
        $client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);

        $user = $userRepository->findOneBy(['email' => 'admin@punaises.fr']);

        $client->loginUser($user);
        $client->request('GET', $route);
        $this->assertResponseIsSuccessful($signalement->getId());
        $this->assertSelectorTextContains('.suivi-usager h2',
            'Votre signalement #'.$signalement->getReference()
        );
    }

    public function provideRoutes(): \Generator
    {
        /** @var SignalementRepository $signalementRepository */
        $signalementRepository = static::getContainer()->get(SignalementRepository::class);
        /** @var UrlGeneratorInterface $generatorUrl */
        $generatorUrl = static::getContainer()->get(UrlGeneratorInterface::class);

        $signalements = $signalementRepository->findAll();

        /** @var Signalement $signalement */
        foreach ($signalements as $signalement) {
            $route = $generatorUrl->generate('app_suivi_usager_view', ['uuidPublic' => $signalement->getUuidPublic()]);
            yield $route => [$route, $signalement];
        }
    }

    public function testSignalementBasculePro(): void
    {
        $client = static::createClient();
        /** @var SignalementRepository $signalementRepository */
        $signalementRepository = static::getContainer()->get(SignalementRepository::class);
        $signalement = $signalementRepository->findOneBy(['autotraitement' => 1]);
        /** @var RouterInterface $router */
        $router = self::getContainer()->get(RouterInterface::class);
        $routePostSignalement = $router->generate(
            'app_signalement_switch_pro',
            [
                'uuid' => $signalement->getUuid(),
                '_csrf_token' => $this->generateCsrfToken($client, 'signalement_switch_pro'),
            ]
        );
        $client->request('POST', $routePostSignalement);
        $this->assertEmailCount(1);
        $this->assertResponseRedirects('/signalements/'.$signalement->getUuidPublic());
    }
}
