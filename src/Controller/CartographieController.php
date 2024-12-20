<?php

namespace App\Controller;

use App\Dto\CartographieRequest;
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
        if ($request->get('load_markers')) {
            if ($request->get('filter-date')) {
                $date = \DateTimeImmutable::createFromFormat('j/m/Y', $request->get('filter-date'));
            } else {
                $date = new \DateTimeImmutable();
            }
            $cartoRequest = new CartographieRequest(
                $request->get('swLat'),
                $request->get('swLng'),
                $request->get('neLat'),
                $request->get('neLng'),
                $date,
            );

            $signalements = $signalementRepository->findAllWithGeoData($cartoRequest);

            return $this->json(
                [
                    'signalements' => $cartoStatutCalculator->calculate($signalements, $date),
                ]
            );
        }

        return $this->render('cartographie/index.html.twig');
    }
}
