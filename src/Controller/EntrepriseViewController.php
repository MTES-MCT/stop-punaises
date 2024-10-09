<?php

namespace App\Controller;

use App\Entity\Employe;
use App\Entity\Entreprise;
use App\Entity\Enum\Status;
use App\Event\EntrepriseUpdatedEvent;
use App\Form\EmployeType;
use App\Form\EntrepriseType;
use App\Manager\EmployeManager;
use App\Manager\EntrepriseManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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

    #[Route('/bo/entreprises/{uuid}/switch_status', name: 'app_entreprise_switch_status')]
    public function switchStatus(
        Entreprise $entreprise,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        if ($this->isCsrfTokenValid('switch_status', $request->get('_t'))) {
            if (Status::ACTIVE === $entreprise->getUser()->getStatus()) {
                $entreprise->getUser()->setStatus(Status::ARCHIVE);
                $this->addFlash('success', 'L\'entreprise "'.$entreprise->getNom().'" (id : '.$entreprise->getId().') a été archivée.');
            } elseif (Status::ARCHIVE === $entreprise->getUser()->getStatus()) {
                $entreprise->getUser()->setStatus(Status::ACTIVE);
                $this->addFlash('success', 'L\'entreprise "'.$entreprise->getNom().'" (id : '.$entreprise->getId().') a été désarchivée.');
            }
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_entreprise_list');
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
        ?Entreprise $entreprise,
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
