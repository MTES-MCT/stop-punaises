<?php

namespace App\Controller;

use App\Entity\Employe;
use App\Entity\Entreprise;
use App\Form\EmployeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EntrepriseViewController extends AbstractController
{
    #[Route('/bo/entreprises/{uuid}', name: 'app_entreprise_view')]
    public function index(Request $request, Entreprise $entreprise, EntityManagerInterface $entityManager): Response
    {
        if (!$entreprise) {
            return $this->render('entreprise_view/not-found.html.twig');
        }

        $employe = new Employe();
        $employe->setUuid(uniqid());
        $feedback = [];
        $formCreateEmploye = $this->createForm(EmployeType::class, $employe);
        $formCreateEmploye->handleRequest($request);
        if ($formCreateEmploye->isSubmitted()) {
            if ($formCreateEmploye->isValid()) {
                $employe->setEntreprise($entreprise);
                $entityManager->persist($employe);
                $entityManager->flush();

                return $this->redirect($this->generateUrl('app_entreprise_view', ['uuid' => $entreprise->getUuid()]).'?create_success_message=1');
            }
            $feedback[] = 'Il y a des erreurs dans les données transmises.';
        }

        return $this->render('entreprise_view/index.html.twig', [
            'entreprise' => $entreprise,
            'formCreateEmploye' => $formCreateEmploye->createView(),
            'display_signalement_create_success' => '1' == $request->get('create_success_message'),
            'feedback' => $feedback,
        ]);
    }
}
