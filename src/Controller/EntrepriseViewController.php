<?php

namespace App\Controller;

use App\Entity\Employe;
use App\Entity\Entreprise;
use App\Event\EntrepriseUpdatedEvent;
use App\Form\EmployeType;
use App\Form\EntrepriseType;
use App\Manager\EmployeManager;
use App\Manager\EntrepriseManager;
use App\Manager\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EntrepriseViewController extends AbstractController
{
    #[Route('/bo/entreprises/{uuid}', name: 'app_entreprise_view')]
    public function index(Request $request,
                          Entreprise $entreprise,
                          UserManager $userManager,
                          Security $security,
                          EntityManagerInterface $entityManager,
                          EntrepriseManager $entrepriseManager,
                          EmployeManager $employeManager,
                          EventDispatcherInterface $eventDispatcher): Response
    {
        $this->denyAccessUnlessGranted('ENTREPRISE_VIEW', $entreprise);

        $formEditEntreprise = $this->createForm(EntrepriseType::class, $entreprise);
        $formEditEntreprise->handleRequest($request);
        if ($formEditEntreprise->isSubmitted() && $formEditEntreprise->isValid()) {
            $entrepriseManager->save($entreprise);
            $this->dispatchEntrepriseUpdateEvent($eventDispatcher, $formEditEntreprise, $entreprise);
            $this->addFlash('success', 'Les informations de l\'entreprise ont été modifiées avec succès.');

            return $this->redirect($this->generateUrl('app_entreprise_view', ['uuid' => $entreprise->getUuid()]));
        }

        $this->displayErrors($formEditEntreprise);

        $editEmployeUuid = $request->get('editEmploye');
        $employe = new Employe();
        $employe->setUuid(uniqid());
        $formCreateEmploye = $this->createForm(EmployeType::class, $employe);
        if (empty($editEmployeUuid)) {
            $formCreateEmploye->handleRequest($request);
            if ($formCreateEmploye->isSubmitted() && $formCreateEmploye->isValid()) {
                $employe->setEntreprise($entreprise);
                $employeManager->save($employe);
                $this->addFlash('success', 'L\'employé a été ajouté.');

                return $this->redirect($this->generateUrl('app_entreprise_view', ['uuid' => $entreprise->getUuid()]));
            }
            $this->displayErrors($formCreateEmploye);
        }

        $formsEditEmploye = [];
        $employes = $entreprise->getEmployes();
        if (\count($employes) > 0) {
            foreach ($employes as $employe) {
                $formEditEmploye = $this->createForm(EmployeType::class, $employe);
                if ($employe->getUuid() == $editEmployeUuid) {
                    $formEditEmploye->handleRequest($request);
                    if ($formEditEmploye->isSubmitted() && $formEditEmploye->isValid()) {
                        $employeManager->save($employe);
                        $this->addFlash('success', 'Les informations de l\'employé ont été modifiées avec succès.');

                        return $this->redirect($this->generateUrl('app_entreprise_view', ['uuid' => $entreprise->getUuid()]));
                    }
                    $this->displayErrors($formEditEmploye);
                }
                $formsEditEmploye[$employe->getUuid()] = $formEditEmploye->createView();
            }
        }

        return $this->render('entreprise_view/index.html.twig', [
            'entreprise' => $entreprise,
            'formCreateEmploye' => $formCreateEmploye->createView(),
            'formEditEntreprise' => $formEditEntreprise->createView(),
            'formsEditEmploye' => $formsEditEmploye,
        ]);
    }

    private function displayErrors(FormInterface $form): void
    {
        /** @var FormError $error */
        foreach ($form->getErrors(true) as $error) {
            $this->addFlash('error', $error->getMessage());
        }
    }

    private function dispatchEntrepriseUpdateEvent(
        EventDispatcherInterface $eventDispatcher,
        FormInterface $form,
        Entreprise $entreprise,
    ): void {
        $currentEmail = $entreprise?->getUser()?->getEmail();
        $newEmail = $form->getData()->getEmail();
        if ($newEmail !== $currentEmail) {
            $eventDispatcher->dispatch(
                new EntrepriseUpdatedEvent($entreprise, $currentEmail),
                EntrepriseUpdatedEvent::NAME
            );
        }
    }
}
