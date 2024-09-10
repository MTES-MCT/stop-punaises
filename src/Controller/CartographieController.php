<?php

namespace App\Controller;

use App\Repository\SignalementRepository;
use App\Service\Signalement\CartoStatutCalculator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartographieController extends AbstractController
{
    #[Route('/bo/cartographie', name: 'app_cartographie')]
    public function index(
        SignalementRepository $signalementRepository,
        CartoStatutCalculator $cartoStatutCalculator,
        Request $request,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        if ($request->get('load_markers')) {
            if ($request->get('filter-date')) {
                $date = \DateTimeImmutable::createFromFormat('j/m/Y', $request->get('filter-date'));
            } else {
                $date = new \DateTimeImmutable();
            }
            $signalements = $signalementRepository->findAllWithGeoData(
                $date,
                (int) $request->get('offset')
            );

            return $this->json(
                [
                    'signalements' => $cartoStatutCalculator->calculate($signalements, $date),
                ]
            );
        }

        return $this->render('cartographie/index.html.twig');
    }
}
