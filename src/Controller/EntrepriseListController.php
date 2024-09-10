<?php

namespace App\Controller;

use App\Entity\Entreprise;
use App\Event\EntrepriseRegisteredEvent;
use App\Form\EntrepriseType;
use App\Manager\EntrepriseManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EntrepriseListController extends AbstractController
{
    #[Route('/bo/entreprises', name: 'app_entreprise_list')]
    public function index(
        Request $request,
        EntrepriseManager $entrepriseManager,
        EventDispatcherInterface $eventDispatcher,
    ): Response {
        $entreprise = new Entreprise();
        $form = $this->createForm(EntrepriseType::class, $entreprise);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entrepriseManager->save($entreprise);
                $eventDispatcher->dispatch(new EntrepriseRegisteredEvent($entreprise), EntrepriseRegisteredEvent::NAME);
                $this->addFlash('success', 'L\'entreprise a bien été enregistrée.');

                return $this->redirect($this->generateUrl('app_entreprise_list'));
            }
            /** @var FormError $error */
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        $entreprises = $entrepriseManager->findAll();

        return $this->render('entreprise_list/index.html.twig', [
            'form' => $form->createView(),
            'entreprises' => $entreprises,
        ]);
    }
}
