<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontInformationController extends AbstractController
{
    #[Route('/information', name: 'app_front_information')]
    public function index(): Response
    {
        return $this->render('front/information.html.twig', [
            'controller_name' => 'FrontInformationController',
        ]);
    }
}
