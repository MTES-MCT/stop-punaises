<?php

namespace App\Controller;

use App\Manager\SignalementManager;
use App\Repository\EntrepriseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SignalementListController extends AbstractController
{
    #[Route('/bo/signalements', name: 'app_signalement_list')]
    public function index(
        Request $request,
        SignalementManager $signalementManager,
        EntrepriseRepository $entrepriseRepository): Response
    {
        $signalements = $signalementManager->findByPrivileges();

        $entreprises = [];
        if ($this->isGranted('ROLE_ADMIN')) {
            $entreprises = $entrepriseRepository->findAll();
        }

        return $this->render('signalement_list/index.html.twig', [
            'display_signalement_create_success' => '1' == $request->get('create_success_message'),
            'count_signalement' => \count($signalements),
            'signalements' => $signalements,
            'entreprises' => $entreprises,
        ]);
    }
}
