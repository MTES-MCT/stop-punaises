<?php

namespace App\Controller\Front;

use App\Repository\EntreprisePubliqueRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EntreprisesPubliquesController extends AbstractController
{
    #[Route('/entreprises-labellisees', name: 'app_front_entreprises_labellisees')]
    public function signalement(
        Request $request,
        EntreprisePubliqueRepository $entreprisePubliqueRepository,
    ): Response {
        $codePostal = $request->get('code-postal');
        if (empty($codePostal)) {
            return $this->redirectToRoute('home');
        }

        $codePostal = str_pad($codePostal, 5, '0', \STR_PAD_LEFT);
        $zipCode = substr($codePostal, 0, 2);

        $listEntreprisesPubliquesByZipCode = $entreprisePubliqueRepository->findByZipCode($zipCode);

        return $this->render('front/entreprises-labelisees.html.twig', [
            'code_departement' => $zipCode,
            'entreprises_publiques' => $listEntreprisesPubliquesByZipCode,
        ]);
    }
}
