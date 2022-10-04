<?php

namespace App\Controller;

use App\Repository\SignalementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SignalementListController extends AbstractController
{
    #[Route('/bo/signalements', name: 'app_signalement_list')]
    public function index(Request $request, SignalementRepository $signalementRepository): Response
    {
        $signalements = $signalementRepository->findAll();
        return $this->render('signalement_list/index.html.twig', [
            'display_signalement_create_success' => $request->get('create_success_message') == '1',
            'count_signalement' => count($signalements),
            'signalements' => $signalements,
        ]);
    }
}
