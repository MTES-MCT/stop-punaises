<?php

namespace App\Controller;

use App\Manager\SignalementManager;
use App\Repository\EntrepriseRepository;
use App\Repository\SignalementRepository;
use App\Repository\TerritoireRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SignalementListController extends AbstractController
{
    #[Route('/bo/signalements', name: 'app_signalement_list')]
    public function signalements(
        SignalementManager $signalementManager,
        TerritoireRepository $territoireRepository): Response
    {
        $territoires = $territoireRepository->findAll();
        // TODO : changer requête
        $signalements = $signalementManager->findByPrivileges();

        return $this->render('signalement_list/signalements.html.twig', [
            'title' => 'Signalements usagers',
            'count_signalement' => \count($signalements),
            'signalements' => $signalements,
            'territoires' => $territoires,
        ]);
    }

    #[Route('/bo/historique', name: 'app_historique_list')]
    public function historique(
        Request $request,
        SignalementManager $signalementManager,
        EntrepriseRepository $entrepriseRepository): Response
    {
        $signalements = $signalementManager->findHistoriqueEntreprise();

        $entreprises = [];
        if ($this->isGranted('ROLE_ADMIN')) {
            $entreprises = $entrepriseRepository->findAll();
        }

        return $this->render('signalement_list/historique.html.twig', [
            'title' => 'Données historiques',
            'display_signalement_create_success' => '1' == $request->get('create_success_message'),
            'count_signalement' => \count($signalements),
            'signalements' => $signalements,
            'entreprises' => $entreprises,
        ]);
    }

    #[Route('/bo/hors-perimetre', name: 'app_horsperimetre_list')]
    public function horsPerimetre(
        SignalementRepository $signalementRepository,
        TerritoireRepository $territoireRepository): Response
    {
        $territoires = $territoireRepository->findAll();
        $signalements = $signalementRepository->findFromInactiveTerritories();

        return $this->render('signalement_list/hors-perimetre.html.twig', [
            'title' => 'Signalements hors périmètre',
            'count_signalement' => \count($signalements),
            'signalements' => $signalements,
            'territoires' => $territoires,
        ]);
    }
}
