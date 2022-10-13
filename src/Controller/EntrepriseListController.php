<?php

namespace App\Controller;

use App\Entity\Entreprise;
use App\Form\EntrepriseType;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EntrepriseListController extends AbstractController
{
    #[Route('/bo/entreprises', name: 'app_entreprise_list')]
    public function index(Request $request, EntrepriseRepository $entrepriseRepository, EntityManagerInterface $entityManager): Response
    {
        $entreprise = new Entreprise();
        $entreprise->setUuid(uniqid());
        $feedback = [];
        $form = $this->createForm(EntrepriseType::class, $entreprise);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->persist($entreprise);
                $entityManager->flush();

                return $this->redirect($this->generateUrl('app_entreprise_list').'?create_success_message=1');
            }
            $feedback[] = 'Il y a des erreurs dans les données transmises.';
        }

        $entreprises = $entrepriseRepository->findAll();

        $isAdmin = $this->isGranted('ROLE_ADMIN');

        return $this->render('entreprise_list/index.html.twig', [
            'is_admin' => $isAdmin,
            'form' => $form->createView(),
            'display_signalement_create_success' => '1' == $request->get('create_success_message'),
            'feedback' => $feedback,
            'entreprises' => $entreprises,
            'count_entreprises' => \count($entreprises),
        ]);
    }
}
