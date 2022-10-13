<?php

namespace App\Controller;

use App\Entity\Signalement;
use App\Form\SignalementType;
use App\Repository\EntrepriseRepository;
use App\Repository\SignalementRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SignalementCreateController extends AbstractController
{
    #[Route('/bo/signalements/ajout', name: 'app_signalement_create')]
    public function index(Request $request, EntityManagerInterface $entityManager, SignalementRepository $signalementRepository): Response
    {
        $signalement = new Signalement();
        $signalement->setUuid(uniqid());
        $feedback = [];
        $form = $this->createForm(SignalementType::class, $signalement);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $signalement->setCreatedAt(new DateTimeImmutable());

                $lastReference = $signalementRepository->findLastReference();
                if (!empty($lastReference)) {
                    list($year, $id) = explode('-', $lastReference['reference']);
                    $signalement->setReference($year.'-'.(int) $id + 1);
                } else {
                    $year = (new \DateTime())->format('Y');
                    $signalement->setReference($year.'-'. 1);
                }

                $entityManager->persist($signalement);
                $entityManager->flush();

                return $this->redirect($this->generateUrl('app_signalement_list').'?create_success_message=1');
            }
            $feedback[] = 'Il y a des erreurs dans les données transmises.';
        }

        $entreprise = null;
        $isAdmin = $this->isGranted('ROLE_ADMIN');
        if (!$isAdmin) {
            // $entreprise = TODO : entreprise de l'utilisateur en cours
        }

        return $this->render('signalement_create/index.html.twig', [
            'is_admin' => $isAdmin,
            'form' => $form->createView(),
            'feedback' => $feedback,
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
}
