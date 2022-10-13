<?php

namespace App\Controller;

use App\Entity\Entreprise;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EntrepriseViewController extends AbstractController
{
    #[Route('/bo/entreprises/{uuid}', name: 'app_entreprise_view')]
    public function index(Entreprise $entreprise): Response
    {
        if (!$entreprise) {
            return $this->render('entreprise_view/not-found.html.twig');
        }

        return $this->render('entreprise_view/index.html.twig', [
            'entreprise' => $entreprise,
        ]);
    }
}
