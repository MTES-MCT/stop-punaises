<?php

namespace App\Controller;

use App\Entity\Signalement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SignalementViewController extends AbstractController
{
    #[Route('/bo/signalements/{uuid}', name: 'app_signalement_view')]
    public function index(Signalement $signalement): Response
    {
        if (!$signalement) {
            return $this->render('signalement_view/not-found.html.twig');
        }

        $isAdmin = $this->isGranted('ROLE_ADMIN');

        return $this->render('signalement_view/index.html.twig', [
            'is_admin' => $isAdmin,
            'signalement' => $signalement,
        ]);
    }
}
