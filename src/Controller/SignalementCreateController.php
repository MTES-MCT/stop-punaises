<?php

namespace App\Controller;

use App\Form\SignalementType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SignalementCreateController extends AbstractController
{
    #[Route('/signaler', name: 'app_signalement_create')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(SignalementType::class);
        $form->handleRequest($request);
        
        return $this->render('signalement_create/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
