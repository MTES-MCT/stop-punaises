<?php

namespace App\Controller;

use App\Entity\Signalement;
use App\Form\SignalementType;
use App\Manager\SignalementManager;
use App\Repository\EntrepriseRepository;
use App\Service\Signalement\ReferenceGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SignalementCreateController extends AbstractController
{
    #[Route('/bo/signalements/ajout', name: 'app_signalement_create')]
    public function index(Request $request, SignalementManager $signalementManager, ReferenceGenerator $referenceGenerator): Response
    {
        $signalement = new Signalement();
        $form = $this->createForm(SignalementType::class, $signalement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $signalement->setReference($referenceGenerator->generate());
            $signalementManager->save($signalement);

            $this->addFlash('success', 'Le signalement a bien été enregistré.');

            return $this->redirect($this->generateUrl('app_signalement_list'));
        }

        $this->displayErrors($form);

        return $this->render('signalement_create/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/bo/signalements/ajout/liste-employes', name: 'app_liste_employes')]
    public function get_list_employes(Request $request, EntrepriseRepository $entrepriseRepository): Response
    {
        $idEntreprise = $request->get('idEntreprise');
        $entreprise = $entrepriseRepository->findOneBy(['id' => $idEntreprise]);

        if (empty($entreprise)) {
            return $this->json(['success' => true, 'data' => '[]']);
        }

        $jsonData = [];
        $employes = $entreprise->getEmployes();
        foreach ($employes as $employe) {
            $jsonData[$employe->getId()] = $employe->getPrenom().' '.$employe->getNom();
        }

        return $this->json(['success' => true, 'data' => $jsonData]);
    }

    private function displayErrors(FormInterface $form): void
    {
        /** @var FormError $error */
        foreach ($form->getErrors(true) as $error) {
            $this->addFlash('error', $error->getMessage());
        }
    }
}
