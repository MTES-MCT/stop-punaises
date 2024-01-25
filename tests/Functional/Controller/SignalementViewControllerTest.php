<?php

namespace App\Tests\Functional\Controller;

use App\Repository\InterventionRepository;
use App\Repository\SignalementRepository;
use App\Repository\UserRepository;
use App\Tests\SessionHelper;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

class SignalementViewControllerTest extends WebTestCase
{
    use SessionHelper;

    public function testSignalementSuccessfullyDisplay(): void
    {
        $client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);

        $user = $userRepository->findOneBy(['email' => 'admin@punaises.fr']);
        /** @var SignalementRepository $signalementRepository */
        $signalementRepository = static::getContainer()->get(SignalementRepository::class);
        $date = new \DateTime();
        $year = $date->format('Y');
        $signalement = $signalementRepository->findOneBy(['reference' => $year.'-1']);

        $client->loginUser($user);

        /** @var RouterInterface $router */
        $router = self::getContainer()->get(RouterInterface::class);
        $routePostSignalement = $router->generate('app_signalement_view', ['uuid' => $signalement->getUuid()]);
        $client->request('GET', $routePostSignalement);

        $this->assertResponseIsSuccessful($signalement->getId());
    }

    public function testAcceptSignalement(): void
    {
        $client = static::createClient();

        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);

        $user = $userRepository->findOneBy(['email' => 'company-01@punaises.fr']);
        /** @var SignalementRepository $signalementRepository */
        $signalementRepository = static::getContainer()->get(SignalementRepository::class);
        $date = new \DateTime();
        $year = $date->format('Y');
        $signalement = $signalementRepository->findOneBy(['reference' => $year.'-1']);

        $client->loginUser($user);

        /** @var RouterInterface $router */
        $router = self::getContainer()->get(RouterInterface::class);
        $routePostSignalement = $router->generate(
            'app_signalement_intervention_accept',
            [
                'uuid' => $signalement->getUuid(),
                '_csrf_token' => $this->generateCsrfToken($client, 'signalement_intervention_accept'),
            ]
        );
        $client->request('POST', $routePostSignalement);

        $this->assertResponseRedirects('/bo/signalements/'.$signalement->getUuid());

        /** @var InterventionRepository $interventionRepository */
        $interventionRepository = static::getContainer()->get(InterventionRepository::class);

        $intervention = $interventionRepository->findBy([
            'signalement' => $signalement,
            'entreprise' => $user->getEntreprise(),
        ]);
        $this->assertCount(1, $intervention);

        // on rappelle la route une deuxième fois pour vérifier qu'une deuxième intervention n'est pas créée
        $client->request('POST', $routePostSignalement);

        $intervention = $interventionRepository->findBy([
            'signalement' => $signalement,
            'entreprise' => $user->getEntreprise(),
        ]);
        $this->assertCount(1, $intervention);
    }
}
