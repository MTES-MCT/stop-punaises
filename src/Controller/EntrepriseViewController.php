<?php

namespace App\Controller;

use App\Entity\Employe;
use App\Entity\Entreprise;
use App\Form\EmployeType;
use App\Form\EntrepriseType;
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

        $isAdmin = $this->isGranted('ROLE_ADMIN');

        $feedbackEditEntreprise = [];
        $initEntrepriseEmail = $entreprise->getEmail();
        $formEditEntreprise = $this->createForm(EntrepriseType::class, $entreprise);
        $formEditEntreprise->handleRequest($request);
        if ($formEditEntreprise->isSubmitted()) {
            if ($formEditEntreprise->isValid()) {
                $entityManager->persist($entreprise);
                $entityManager->flush();

                if ($entreprise->getEmail() !== $initEntrepriseEmail) {
                    // TODO :
                    // Si ROLE_ENTREPRISE : Logout, redirect to login with success message
                    // Dans tous les cas : envoi d'un mail d'activation de compte
                }

                return $this->redirect($this->generateUrl('app_entreprise_view', ['uuid' => $entreprise->getUuid(), 'edit_success_message' => 1]));
            }
            $feedbackEditEntreprise[] = 'Il y a des erreurs dans les données transmises.';
        }

        $employe = new Employe();
        $employe->setUuid(uniqid());
        $feedbackCreateEmploye = [];
        $formCreateEmploye = $this->createForm(EmployeType::class, $employe);
        $formCreateEmploye->handleRequest($request);
        if ($formCreateEmploye->isSubmitted()) {
            if ($formCreateEmploye->isValid()) {
                $employe->setEntreprise($entreprise);
                $entityManager->persist($employe);
                $entityManager->flush();

                return $this->redirect($this->generateUrl('app_entreprise_view', ['uuid' => $entreprise->getUuid(), 'create_success_message' => 1]));
            }
            $feedbackCreateEmploye[] = 'Il y a des erreurs dans les données transmises.';
        }

        return $this->render('entreprise_view/index.html.twig', [
            'is_admin' => $isAdmin,
            'entreprise' => $entreprise,
            'formEditEntreprise' => $formEditEntreprise->createView(),
            'feedbackEditEntreprise' => $feedbackEditEntreprise,
            'formCreateEmploye' => $formCreateEmploye->createView(),
            'feedbackCreateEmploye' => $feedbackCreateEmploye,
            'display_employe_create_success' => '1' == $request->get('create_success_message'),
            'display_entreprise_edit_success' => '1' == $request->get('edit_success_message'),
        ]);
    }
}
