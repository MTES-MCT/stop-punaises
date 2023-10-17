<?php

namespace App\Controller;

use App\Entity\Enum\InfestationLevel;
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
        TerritoireRepository $territoireRepository
    ): Response {
        $territoires = $territoireRepository->findAll();
        $signalements = $signalementManager->findDeclaredByOccupants();
        $entreprise = null;
        if (!$this->isGranted('ROLE_ADMIN')) {
            $entreprise = $this->getUser()->getEntreprise();
        }

        return $this->render('signalement_list/signalements.html.twig', [
            'count_signalement' => \count($signalements),
            'signalements' => $signalements,
            'territoires' => $territoires,
            'niveaux_infestation' => InfestationLevel::getLabelList(),
            'entreprise' => $entreprise,
        ]);
    }

    #[Route('/bo/historique', name: 'app_historique_list')]
    public function historique(
        Request $request,
        SignalementManager $signalementManager,
        EntrepriseRepository $entrepriseRepository
    ): Response {
        $signalements = $signalementManager->findHistoriqueEntreprise();

        $entreprises = [];
        if ($this->isGranted('ROLE_ADMIN')) {
            $entreprises = $entrepriseRepository->findAll();
        }

        return $this->render('signalement_list/historique.html.twig', [
            'display_signalement_create_success' => '1' == $request->get('create_success_message'),
            'count_signalement' => \count($signalements),
            'signalements' => $signalements,
            'entreprises' => $entreprises,
            'niveaux_infestation' => InfestationLevel::getLabelList(),
        ]);
    }

    #[Route('/bo/hors-perimetre', name: 'app_horsperimetre_list')]
    public function horsPerimetre(
        SignalementRepository $signalementRepository,
        TerritoireRepository $territoireRepository
    ): Response {
        $territoires = $territoireRepository->findAll();
        $signalements = $signalementRepository->findFromInactiveTerritories();

        return $this->render('signalement_list/hors-perimetre.html.twig', [
            'count_signalement' => \count($signalements),
            'signalements' => $signalements,
            'territoires' => $territoires,
        ]);
    }

    #[Route('/bo/erp-transports', name: 'app_erptransports_list')]
    public function erpTransports(
        SignalementRepository $signalementRepository,
        TerritoireRepository $territoireRepository
    ): Response {
        $territoires = $territoireRepository->findAll();
        $signalements = $signalementRepository->findErpTransportsSignalements();

        return $this->render('signalement_list/erp-transports.html.twig', [
            'count_signalement' => \count($signalements),
            'signalements' => $signalements,
            'territoires' => $territoires,
        ]);
    }
}
