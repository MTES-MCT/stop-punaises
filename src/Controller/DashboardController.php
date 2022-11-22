<?php

namespace App\Controller;

use App\Repository\SignalementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/bo', name: 'app_dashboard_home')]
    public function index(
        SignalementRepository $signalementRepository,
        ): Response {
        $countNouveaux = 0; // TODO
        $countEnCours = 0; // TODO
        $countHorsPerimetres = 0;
        if ($this->isGranted('ROLE_ADMIN')) {
            $signalements = $signalementRepository->findFromInactiveTerritories();
            $countHorsPerimetres = \count($signalements);
        }

        return $this->render('dashboard/index.html.twig', [
            'count_nouveaux' => $countNouveaux,
            'count_en_cours' => $countEnCours,
            'count_hors_perimetre' => $countHorsPerimetres,
        ]);
    }
}
