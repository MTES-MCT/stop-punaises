<?php

namespace App\Controller\Front;

use App\Form\ContactType;
use App\FormHandler\ContactFormHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    private bool $isSignalementsDisabled;

    public function __construct(
        ParameterBagInterface $parameterBag,
    ) {
        $this->isSignalementsDisabled = $parameterBag->get('is_signalements_disabled');
    }

    #[Route(
        '/',
        name: 'home',
        defaults: ['show_sitemap' => true]
    )]
    public function index(): Response
    {
        return $this->render('front/index.html.twig', ['is_signalements_disabled' => $this->isSignalementsDisabled]);
    }

    #[Route(
        '/signalement',
        name: 'app_front_signalement_type_list',
        defaults: ['show_sitemap' => true]
    )]
    public function signalementList(ParameterBagInterface $parameterBag): Response
    {
        if ($this->isSignalementsDisabled) {
            return $this->redirectToRoute('home');
        }

        return $this->render('front/signalement-type-list.html.twig', [
            'feature_three_forms' => $parameterBag->get('feature_three_forms'),
        ]);
    }

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
        #[Target('forms')] RateLimiterFactoryInterface $rateLimiter,
    ): Response {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $limiter = $rateLimiter->create($request->getClientIp().'_contact_form');
                if (false === $limiter->consume(1)->isAccepted()) {
                    $this->addFlash('error', 'Vous avez atteint le nombre maximum de messages que vous pouvez envoyer. Veuillez réessayer plus tard.');

                    return $this->redirectToRoute('app_front_contact');
                }
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
