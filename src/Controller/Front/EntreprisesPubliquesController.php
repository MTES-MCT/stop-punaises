<?php

namespace App\Controller\Front;

use App\Repository\EntreprisePubliqueRepository;
use App\Service\Signalement\ZipCodeProvider;
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
        ZipCodeProvider $zipCodeProvider,
    ): Response {
        $codePostal = $request->query->get('code-postal');
        if (empty($codePostal)) {
            return $this->redirectToRoute('home');
        }

        $order = $request->query->get('order');
        if (empty($order)) {
            $order = 'random';
        }

        $filter = $request->query->get('filter');
        if (empty($filter)) {
            $filter = 'all';
        }

        $codePostal = str_pad($codePostal, 5, '0', \STR_PAD_LEFT);
        $zipCode = $zipCodeProvider->getByCodePostal($codePostal);

        $listEntreprisesPubliquesByZipCode = $entreprisePubliqueRepository->findByZipCodeAndFilter($zipCode, $order, $filter);

        return $this->render('front/entreprises-labelisees.html.twig', [
            'code_postal' => $request->query->get('code-postal'),
            'order' => $request->query->get('order'),
            'filter' => $request->query->get('filter'),
            'code_departement' => $zipCode,
            'entreprises_publiques' => $listEntreprisesPubliquesByZipCode,
        ]);
    }
}
