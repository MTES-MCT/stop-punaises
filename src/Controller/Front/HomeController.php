<?php

namespace App\Controller\Front;

use App\Form\ContactType;
use App\FormHandler\ContactFormHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('front/index.html.twig', []);
    }

    #[Route('/signalement', name: 'app_front_signalement_type_list')]
    public function signalementList(ParameterBagInterface $parameterBag): Response
    {
        return $this->render('front/signalement-type-list.html.twig', [
            'feature_three_forms' => $parameterBag->get('feature_three_forms'),
        ]);
    }

    #[Route('/information', name: 'app_front_information')]
    public function information(): Response
    {
        return $this->render('front/information.html.twig', [
            'controller_name' => 'FrontInformationController',
        ]);
    }

    #[Route('/mentions-legales', name: 'app_front_mentions_legales')]
    public function mentionsLegales(): Response
    {
        return $this->render('front/mentions-legales.html.twig', [
        ]);
    }

    #[Route('/politique-de-confidentialite', name: 'app_front_politique_confidentialite')]
    public function politiqueConfidentialite(): Response
    {
        return $this->render('front/politique-de-confidentialite.html.twig', [
        ]);
    }

    #[Route('/contact', name: 'app_front_contact')]
    public function contact(
        Request $request,
        ContactFormHandler $contactFormHandler,
    ): Response {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $contactFormHandler->handle($form);
                $this->addFlash('success', 'Votre message à bien été envoyé !');
            } else {
                $this->addFlash('error', 'Une erreur a empêché l\'envoi de votre message');
            }
        }

        return $this->render('front/contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
