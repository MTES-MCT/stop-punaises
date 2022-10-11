<?php

namespace App\Controller;

use App\Repository\EntrepriseRepository;
use App\Repository\SignalementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SignalementViewController extends AbstractController
{
    #[Route('/bo/signalements/{uuid}', name: 'app_signalement_view')]
    public function index(string $uuid, SignalementRepository $signalementRepository): Response
    {
        /** @var Signalement $signalement */
        $signalement = $signalementRepository->findOneByUuid($uuid);

        if (!$signalement) {
            return $this->render('signalement_view/not-found.html.twig');
        }

        return $this->render('signalement_view/index.html.twig', [
            'signalement' => $signalement,
        ]);
    }
}
