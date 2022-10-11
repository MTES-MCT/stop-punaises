<?php

namespace App\Controller;

use App\Repository\EntrepriseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EntrepriseListController extends AbstractController
{
    #[Route('/bo/entreprises', name: 'app_entreprise_list')]
    public function index(EntrepriseRepository $entrepriseRepository): Response
    {
        // TODO : test isAdmin
        $entreprises = $entrepriseRepository->findAll();

        return $this->render('entreprise_list/index.html.twig', [
            'entreprises' => $entreprises,
            'count_entreprises' => \count($entreprises),
        ]);
    }
}
