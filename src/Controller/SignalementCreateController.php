<?php

namespace App\Controller;

use App\Entity\Signalement;
use App\Form\SignalementType;
use App\Repository\EmployeRepository;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SignalementCreateController extends AbstractController
{
    #[Route('/signaler', name: 'app_signalement_create')]
    public function index(Request $request, EntityManagerInterface $entityManager, EntrepriseRepository $entrepriseRepository, EmployeRepository $employeRepository): Response
    {
        $signalement = new Signalement();
        $signalement->setUuid(uniqid());
        $feedback = [];
        $form = $this->createForm(SignalementType::class, $signalement);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $idAgent = $request->get('agent');
                $employe = $employeRepository->findOneBy(['id' => $idAgent]);
                $signalement->setAgent($employe);

                $entityManager->persist($signalement);
                $entityManager->flush();

                return $this->redirect($this->generateUrl('app_signalement_create_success'));
                
            } else {

                dd($form);
                $feedback []= 'Il y a des erreurs dans les données transmises.';
            }
        }
        
        return $this->render('signalement_create/index.html.twig', [
            'form' => $form->createView(),
            'feedback' => $feedback,
        ]);
    }

    #[Route('/signaler/succes', name: 'app_signalement_create_success')]
    public function index_success(Request $request)
    {
        return $this->render('signalement_create/success.html.twig');
    }

    #[Route('/liste-employes', name: 'app_liste_employes')]
    public function get_list_employes(Request $request, EntrepriseRepository $entrepriseRepository): Response
    {
        // TODO : sécurité pour bloquer l'accès
        $idEntreprise = $request->get('idEntreprise');
        $entreprise = $entrepriseRepository->findOneBy(['id' => $idEntreprise]);

        if (empty($entreprise)) {
            return $this->json(['success' => true, 'data' => '[]']);
        }

        $jsonData = [];
        $employes = $entreprise->getEmployes();
        foreach ($employes as $employe) {
            $jsonData[$employe->getId()] = $employe->getPrenom() . ' ' . $employe->getNom();
        }

        return $this->json(['success' => true, 'data' => $jsonData]);
    }
}
