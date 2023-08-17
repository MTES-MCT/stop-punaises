<?php

namespace App\Controller;

use App\Entity\Signalement;
use App\Event\InterventionEntrepriseResolvedEvent;
use App\Form\SignalementType;
use App\Manager\InterventionManager;
use App\Manager\SignalementManager;
use App\Repository\InterventionRepository;
use App\Service\Mailer\MailerProvider;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SignalementResolveController extends AbstractController
{
    #[Route('/bo/signalements/{uuid}/traiter', name: 'app_signalement_treated')]
    public function index(
        Request $request,
        Signalement $signalement,
        SignalementManager $signalementManager,
        InterventionRepository $interventionRepository,
        InterventionManager $interventionManager,
        MailerProvider $mailerProvider,
        EventDispatcherInterface $eventDispatcher,
        ): Response {
        $form = $this->createForm(SignalementType::class, $signalement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $signalement->updateUuidPublic();
            $signalementManager->save($signalement);

            $this->addFlash('success', 'Le traitement a été marqué comme effectué. Un email de suivi sera envoyé à l\'usager dans 30 jours.');

            /** @var User $user */
            $user = $this->getUser();
            $userEntreprise = $user->getEntreprise();
            $intervention = $interventionRepository->findBySignalementAndEntreprise(
                $signalement,
                $userEntreprise
            );
            $intervention->setResolvedByEntrepriseAt(new DateTimeImmutable());
            $interventionManager->save($intervention);

            $mailerProvider->sendSignalementTraitementResolved($signalement, $intervention);

            $eventDispatcher->dispatch(
                new InterventionEntrepriseResolvedEvent(
                    $intervention
                ),
                InterventionEntrepriseResolvedEvent::NAME
            );

            return $this->redirect($this->generateUrl('app_signalement_view', ['uuid' => $signalement->getUuid()]));
        }

        $this->displayErrors($form);

        return $this->render('signalement_create/index.html.twig', [
            'form' => $form->createView(),
            'app_name' => 'app_signalement_treated',
            'uuid' => $signalement->getUuid(),
        ]);
    }

    private function displayErrors(FormInterface $form): void
    {
        /** @var FormError $error */
        foreach ($form->getErrors(true) as $error) {
            $this->addFlash('error', $error->getMessage());
        }
    }
}
