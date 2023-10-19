<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SignalementErpController extends AbstractController
{
    #[Route('/signalement/erp', name: 'app_signalement_erp')]
    public function index(): Response
    {
        return $this->render('front_signalement_erp/index.html.twig', [
            'controller_name' => 'SignalementErpController',
        ]);
    }
}
