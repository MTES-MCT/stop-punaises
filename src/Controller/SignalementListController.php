<?php

namespace App\Controller;

use App\Entity\Enum\InfestationLevel;
use App\Entity\User;
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
        TerritoireRepository $territoireRepository,
    ): Response {
        $territoires = $territoireRepository->findAll();

        $entreprise = null;
        if (!$this->isGranted('ROLE_ADMIN')) {
            /** @var User $user */
            $user = $this->getUser();
            $entreprise = $user->getEntreprise();
        }

        return $this->render('signalement_list/signalements.html.twig', [
            'territoires' => $territoires,
            'niveaux_infestation' => InfestationLevel::getLabelList(),
            'entreprise' => $entreprise,
        ]);
    }

    #[Route('/bo/historique', name: 'app_historique_list')]
    public function historique(
        Request $request,
        SignalementManager $signalementManager,
        EntrepriseRepository $entrepriseRepository,
    ): Response {
        $signalements = $signalementManager->findHistoriqueEntreprise();

        $entreprises = [];
        if ($this->isGranted('ROLE_ADMIN')) {
            $entreprises = $entrepriseRepository->findAll();
        }

        return $this->render('signalement_list/historique.html.twig', [
            'display_signalement_create_success' => '1' == $request->query->get('create_success_message'),
            'signalements' => $signalements,
            'entreprises' => $entreprises,
            'niveaux_infestation' => InfestationLevel::getLabelList(),
        ]);
    }

    #[Route('/bo/hors-perimetre', name: 'app_horsperimetre_list')]
    public function horsPerimetre(
        SignalementRepository $signalementRepository,
        TerritoireRepository $territoireRepository,
    ): Response {
        $territoires = $territoireRepository->findAll();
        $signalements = $signalementRepository->findFromInactiveTerritories();

        return $this->render('signalement_list/hors-perimetre.html.twig', [
            'signalements' => $signalements,
            'territoires' => $territoires,
        ]);
    }

    #[Route('/bo/erp-transports', name: 'app_erptransports_list')]
    public function erpTransports(
        SignalementRepository $signalementRepository,
        TerritoireRepository $territoireRepository,
    ): Response {
        $territoires = $territoireRepository->findAll();
        $signalements = $signalementRepository->findErpTransportsSignalements();

        return $this->render('signalement_list/erp-transports.html.twig', [
            'signalements' => $signalements,
            'territoires' => $territoires,
        ]);
    }
}
