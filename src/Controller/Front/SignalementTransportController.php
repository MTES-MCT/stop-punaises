<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SignalementTransportController extends AbstractController
{
    #[Route('/signalement/transport', name: 'app_signalement_transport')]
    public function index(): Response
    {
        return $this->render('front_signalement_transport/index.html.twig', [
            'controller_name' => 'SignalementTransportController',
        ]);
    }
}
