<?php

namespace App\Controller;

use App\Repository\SignalementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartographieController extends AbstractController
{
    #[Route('/bo/cartographie', name: 'app_cartographie')]
    public function index(
        SignalementRepository $signalementRepository,
        Request $request
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        if ($request->get('load_markers')) {
            return $this->json(
                [
                'signalements' => $signalementRepository->findAllWithGeoData(
                    (int) $request->get('offset')
                ), ]
            );
        }

        return $this->render('cartographie/index.html.twig');
    }
}
