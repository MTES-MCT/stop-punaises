<?php

namespace App\Controller\Front;

use App\Form\ContactType;
use App\FormHandler\ContactFormHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Cache(public: true, maxage: 3600)]
    #[Route(
        '/',
        name: 'home',
        defaults: ['show_sitemap' => true]
    )]
    public function index(): Response
    {
        return $this->render('front/index.html.twig', []);
    }

    #[Cache(public: true, maxage: 3600)]
    #[Route(
        '/signalement',
        name: 'app_front_signalement_type_list',
        defaults: ['show_sitemap' => true]
    )]
    public function signalementList(ParameterBagInterface $parameterBag): Response
    {
        return $this->render('front/signalement-type-list.html.twig', [
            'feature_three_forms' => $parameterBag->get('feature_three_forms'),
        ]);
    }

    #[Cache(public: true, maxage: 3600)]
    #[Route(
        '/information',
        name: 'app_front_information',
        defaults: ['show_sitemap' => true]
    )]
    public function information(): Response
    {
        return $this->render('front/information.html.twig', [
            'controller_name' => 'FrontInformationController',
        ]);
    }

    #[Cache(public: true, maxage: 3600)]
    #[Route(
        '/accessibilite',
        name: 'app_front_accessibilite',
        defaults: ['show_sitemap' => true]
    )]
    public function accessibilite(): Response
    {
        return $this->render('front/accessibilite.html.twig', [
        ]);
    }

    #[Cache(public: true, maxage: 3600)]
    #[Route(
        '/mentions-legales',
        name: 'app_front_mentions_legales',
        defaults: ['show_sitemap' => true]
    )]
    public function mentionsLegales(): Response
    {
        return $this->render('front/mentions-legales.html.twig', [
        ]);
    }

    #[Cache(public: true, maxage: 3600)]
    #[Route(
        '/politique-de-confidentialite',
        name: 'app_front_politique_confidentialite',
        defaults: ['show_sitemap' => true]
    )]
    public function politiqueConfidentialite(): Response
    {
        return $this->render('front/politique-de-confidentialite.html.twig', [
        ]);
    }

    #[Route(
        '/contact',
        name: 'app_front_contact',
        defaults: ['show_sitemap' => true]
    )]
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
