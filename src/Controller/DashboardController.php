<?php

namespace App\Controller;

use App\Manager\SignalementManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/bo', name: 'app_dashboard_home')]
    public function index(
        SignalementManager $signalementManager,
        ): Response {
        list($countNouveaux, $countEnCours, $countHorsPerimetres) =
            $signalementManager->countSignalements();

        return $this->render('dashboard/index.html.twig', [
            'count_nouveaux' => $countNouveaux,
            'count_en_cours' => $countEnCours,
            'count_hors_perimetre' => $countHorsPerimetres,
        ]);
    }
}
