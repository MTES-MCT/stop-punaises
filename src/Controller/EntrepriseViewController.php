<?php

namespace App\Controller;

use App\Entity\Employe;
use App\Entity\Entreprise;
use App\Event\EntrepriseUpdatedEvent;
use App\Form\EmployeType;
use App\Form\EntrepriseType;
use App\Manager\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EntrepriseViewController extends AbstractController
{
    #[Route('/bo/entreprises/{uuid}', name: 'app_entreprise_view')]
    public function index(Request $request,
                          Entreprise $entreprise,
                          UserManager $userManager,
                          EntityManagerInterface $entityManager,
                          EventDispatcherInterface $eventDispatcher): Response
    {
        if (!$entreprise) {
            return $this->render('entreprise_view/not-found.html.twig');
        }

        $this->denyAccessUnlessGranted('ENTREPRISE_VIEW', $entreprise);

        $formEditEntreprise = $this->createForm(EntrepriseType::class, $entreprise);
        $formEditEntreprise->handleRequest($request);
        if ($formEditEntreprise->isSubmitted()) {
            if ($formEditEntreprise->isValid()) {
                $entityManager->persist($entreprise);
                $entityManager->flush();
                $currentEmail = $entreprise->getUser()->getEmail();
                $newEmail = $formEditEntreprise->getData()->getEmail();
                if ($newEmail !== $currentEmail) {
                    $eventDispatcher->dispatch(
                        new EntrepriseUpdatedEvent($entreprise, $currentEmail),
                        EntrepriseUpdatedEvent::NAME
                    );
                    // TODO :
                    // Si ROLE_ENTREPRISE : Logout, redirect to login with success message
                    // Dans tous les cas : envoi d'un mail d'activation de compte
                }

                return $this->redirect($this->generateUrl('app_entreprise_view', ['uuid' => $entreprise->getUuid(), 'edit_entreprise_success_message' => 1]));
            }
        }

        $editEmployeUuid = $request->get('editEmploye');

        $employe = new Employe();
        $employe->setUuid(uniqid());
        $formCreateEmploye = $this->createForm(EmployeType::class, $employe);
        if (empty($editEmployeUuid)) {
            $formCreateEmploye->handleRequest($request);
            if ($formCreateEmploye->isSubmitted()) {
                if ($formCreateEmploye->isValid()) {
                    $employe->setEntreprise($entreprise);
                    $entityManager->persist($employe);
                    $entityManager->flush();

                    return $this->redirect($this->generateUrl('app_entreprise_view', ['uuid' => $entreprise->getUuid(), 'create_employe_success_message' => 1]));
                }
            }
        }

        $formsEditEmploye = [];
        $employes = $entreprise->getEmployes();
        if (\count($employes) > 0) {
            foreach ($employes as $employe) {
                $formEditEmploye = $this->createForm(EmployeType::class, $employe);
                if ($employe->getUuid() == $editEmployeUuid) {
                    $formEditEmploye->handleRequest($request);
                    if ($formEditEmploye->isSubmitted()) {
                        if ($formEditEmploye->isValid()) {
                            $entityManager->persist($employe);
                            $entityManager->flush();

                            return $this->redirect($this->generateUrl('app_entreprise_view', ['uuid' => $entreprise->getUuid(), 'edit_employe_success_message' => 1]));
                        }
                    }
                }
                $formsEditEmploye[$employe->getUuid()] = $formEditEmploye->createView();
            }
        }

        return $this->render('entreprise_view/index.html.twig', [
            'entreprise' => $entreprise,
            'formCreateEmploye' => $formCreateEmploye->createView(),
            'formEditEntreprise' => $formEditEntreprise->createView(),
            'formsEditEmploye' => $formsEditEmploye,
            'display_employe_create_success' => '1' == $request->get('create_employe_success_message'),
            'display_employe_edit_success' => '1' == $request->get('edit_employe_success_message'),
            'display_entreprise_edit_success' => '1' == $request->get('edit_entreprise_success_message'),
        ]);
    }
}
