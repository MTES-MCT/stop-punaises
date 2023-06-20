<?php

namespace App\Controller;

use App\Repository\SignalementRepository;
use App\Service\Signalement\CartoStatutService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartographieController extends AbstractController
{
    #[Route('/bo/cartographie', name: 'app_cartographie')]
    public function index(
        SignalementRepository $signalementRepository,
        CartoStatutService $cartoStatutService,
        Request $request
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        if ($request->get('load_markers')) {
            // TODO : envoyer la date du filtre pour ne récupérer que les signalements créés avant cette date
            $date = new \DateTimeImmutable();
            $signalements = $signalementRepository->findAllWithGeoData(
                $date,
                (int) $request->get('offset')
            );

            // TODO : créer un service pour calculer un état en cours / résolu / trace
            return $this->json(
                [
                'signalements' => $cartoStatutService->calculateStatut($signalements, $date),
                ]
            );
        }

        return $this->render('cartographie/index.html.twig');
    }
}
