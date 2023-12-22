<?php

namespace App\Controller\Front;

use App\Entity\Enum\InfestationLevel;
use App\Entity\Intervention;
use App\Entity\MessageThread;
use App\Entity\Signalement;
use App\Event\InterventionUsagerAcceptedEvent;
use App\Event\InterventionUsagerRefusedEvent;
use App\Event\SignalementAdminNoticedEvent;
use App\Event\SignalementClosedEvent;
use App\Event\SignalementResolvedEvent;
use App\Event\SignalementSwitchedEvent;
use App\Manager\InterventionManager;
use App\Manager\SignalementManager;
use App\Repository\EntrepriseRepository;
use App\Repository\EventRepository;
use App\Repository\InterventionRepository;
use App\Service\Mailer\MailerProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SuiviUsagerViewController extends AbstractController
{
    #[Route('/signalements/{uuidPublic}', name: 'app_suivi_usager_view')]
    public function suivi_usager(
        Signalement $signalement,
        InterventionRepository $interventionRepository,
        EventRepository $eventRepository,
    ): Response {
        $events = $eventRepository->findUsagerEvents(
            signalementUuid: $signalement->getUuid(),
        );

        $acceptedInterventions = $interventionRepository->findBy([
            'signalement' => $signalement,
            'accepted' => true,
        ]);
        $interventionsToAnswer = $interventionRepository->findInterventionsWithMissingAnswerFromUsager($signalement);
        $interventionsWithoutChoiceUsager = $interventionRepository->findBy([
            'signalement' => $signalement,
            'accepted' => true,
            'choiceByUsagerAt' => null,
        ]);
        $interventionsAcceptedByUsager = $interventionRepository->findBy([
            'signalement' => $signalement,
            'acceptedByUsager' => true,
        ]);
        $interventionAcceptedByUsager = null;
        if (\count($interventionsAcceptedByUsager) > 0) {
            $interventionAcceptedByUsager = $interventionsAcceptedByUsager[0];
        }

        $docFile = $signalement->isAutotraitement() ? $this->getParameter('doc_autotraitement') : $this->getParameter('doc_domicile');
        $docSize = $signalement->isAutotraitement() ? $this->getParameter('doc_autotraitement_size') : $this->getParameter('doc_domicile_size');

        return $this->render('front_suivi_usager/index.html.twig', [
            'signalement' => $signalement,
            'link_pdf' => $this->getParameter('base_url').'/build/'.$docFile,
            'size_pdf' => $docSize,
            'niveau_infestation' => $signalement->getNiveauInfestation() ? InfestationLevel::from($signalement->getNiveauInfestation())->label() : 'Non déterminée',
            'events' => $events,
            'accepted_interventions' => $acceptedInterventions,
            'accepted_estimations' => $interventionsAcceptedByUsager,
            'interventions_to_answer' => $interventionsToAnswer,
            'is_last_intervention_to_answer' => 1 === \count($interventionsWithoutChoiceUsager),
            'intervention_accepted_by_usager' => $interventionAcceptedByUsager,
        ]);
    }

    #[Route('/signalements/{uuid}/basculer-pro', name: 'app_signalement_switch_pro', methods: 'POST')]
    public function signalement_bascule_pro(
        Request $request,
        Signalement $signalement,
        SignalementManager $signalementManager,
        EventDispatcherInterface $eventDispatcher,
        MailerProvider $mailerProvider,
        EntrepriseRepository $entrepriseRepository,
    ): Response {
        if ($this->isCsrfTokenValid('signalement_switch_pro', $request->get('_csrf_token'))) {
            $this->addFlash('success', 'Votre signalement est transféré ! Les entreprises vont vous contacter au plus vite !');
            $signalement->setAutotraitement(false);
            $signalement->setSwitchedTraitementAt(new \DateTimeImmutable());
            $signalementManager->save($signalement);

            $entreprises = $entrepriseRepository->findByTerritoire($signalement->getTerritoire());
            foreach ($entreprises as $entreprise) {
                if ($entreprise->getUser() && $entreprise->getUser()->getEmail()) {
                    $mailerProvider->sendSignalementNewForPro($entreprise->getUser()->getEmail(), $signalement);
                }
            }

            $eventDispatcher->dispatch(
                new SignalementSwitchedEvent(
                    $signalement
                ),
                SignalementSwitchedEvent::NAME_PRO
            );
        }

        return $this->redirectToRoute('app_suivi_usager_view', ['uuidPublic' => $signalement->getUuidPublic()]);
    }

    #[Route('/signalements/{uuid}/basculer-autotraitement', name: 'app_signalement_switch_autotraitement', methods: 'POST')]
    public function signalement_bascule_autotraitement(
        Request $request,
        Signalement $signalement,
        SignalementManager $signalementManager,
        MailerProvider $mailerProvider,
        EventDispatcherInterface $eventDispatcher,
    ): Response {
        if ($this->isCsrfTokenValid('signalement_switch_autotraitement', $request->get('_csrf_token'))) {
            $this->addFlash('success', 'Votre choix a été enregistré. Vous pouvez consulter le protocole d\'auto-traitement.');
            $signalement->setAutotraitement(true);
            $signalement->setReminderAutotraitementAt(null);
            $signalement->setSwitchedTraitementAt(new \DateTimeImmutable());
            $signalementManager->save($signalement);

            $mailerProvider->sendSignalementValidationWithAutotraitement($signalement);

            $eventDispatcher->dispatch(
                new SignalementSwitchedEvent(
                    $signalement
                ),
                SignalementSwitchedEvent::NAME_AUTOTRAITEMENT
            );
        }

        return $this->redirectToRoute('app_suivi_usager_view', ['uuidPublic' => $signalement->getUuidPublic()]);
    }

    #[Route('/signalements/{uuid}/resoudre', name: 'app_signalement_resolve', methods: 'POST')]
    public function signalement_resolu(
        Request $request,
        Signalement $signalement,
        SignalementManager $signalementManager,
        InterventionRepository $interventionRepository,
        MailerProvider $mailerProvider,
        EventDispatcherInterface $eventDispatcher,
    ): Response {
        if ($this->isCsrfTokenValid('signalement_resolve', $request->get('_csrf_token'))) {
            $this->addFlash('success', 'Votre procédure est terminée !');

            $signalement->setResolvedAt(new \DateTimeImmutable());
            $signalement->updateUuidPublic();
            $signalementManager->save($signalement);

            if (!$signalement->isAutotraitement()) {
                $interventionsAcceptedByUsager = $interventionRepository->findBy([
                    'signalement' => $signalement,
                    'acceptedByUsager' => true,
                ]);
                foreach ($interventionsAcceptedByUsager as $intervention) {
                    $mailerProvider->sendSignalementTraitementResolvedForPro($intervention->getEntreprise()->getUser()->getEmail(), $intervention->getSignalement());
                }
            }

            $eventDispatcher->dispatch(
                new SignalementResolvedEvent(
                    $signalement
                ),
                SignalementResolvedEvent::NAME
            );
        }

        return $this->redirectToRoute('app_suivi_usager_view', ['uuidPublic' => $signalement->getUuidPublic()]);
    }

    #[Route('/signalements/{uuid}/notification-toujours-punaises', name: 'app_signalement_confirm_toujours_punaises', methods: 'POST')]
    public function signalement_confirm_toujours_punaises(
        Request $request,
        Signalement $signalement,
        MailerProvider $mailerProvider,
        EventDispatcherInterface $eventDispatcher,
    ): Response {
        if ($this->isCsrfTokenValid('signalement_confirm_toujours_punaises', $request->get('_csrf_token'))) {
            $this->addFlash('success', 'Stop Punaises a été prévenu de votre retour.');
            $mailerProvider->sendAdminToujoursPunaises($this->getParameter('admin_email'), $signalement);

            $eventDispatcher->dispatch(
                new SignalementAdminNoticedEvent(
                    $signalement
                ),
                SignalementAdminNoticedEvent::NAME
            );
        }

        return $this->redirectToRoute('app_suivi_usager_view', ['uuidPublic' => $signalement->getUuidPublic()]);
    }

    #[Route('/signalements/{uuid}/stop', name: 'app_signalement_stop', methods: 'POST')]
    public function signalement_stop(
        Request $request,
        Signalement $signalement,
        SignalementManager $signalementManager,
        EventDispatcherInterface $eventDispatcher,
    ): Response {
        if ($this->isCsrfTokenValid('signalement_stop', $request->get('_csrf_token'))) {
            $this->addFlash('success', 'Votre procédure est terminée !');
            $signalement->setClosedAt(new \DateTimeImmutable());
            $signalementManager->save($signalement);

            $eventDispatcher->dispatch(
                new SignalementClosedEvent(
                    $signalement
                ),
                SignalementClosedEvent::NAME
            );
        }

        return $this->redirectToRoute('app_suivi_usager_view', ['uuidPublic' => $signalement->getUuidPublic()]);
    }

    #[Route('/interventions/{id}/choix', name: 'app_signalement_estimation_choice', methods: 'POST')]
    public function signalement_choice(
        Request $request,
        Intervention $intervention,
        InterventionManager $interventionManager,
        InterventionRepository $interventionRepository,
        MailerProvider $mailerProvider,
        EventDispatcherInterface $eventDispatcher,
    ): Response {
        if ($this->isCsrfTokenValid('signalement_estimation_choice', $request->get('_csrf_token'))) {
            if ('accept' == $request->get('action')) {
                $this->addFlash('success', 'L\'estimation a bien été acceptée');
                $intervention->setChoiceByUsagerAt(new \DateTimeImmutable());
                $intervention->setAcceptedByUsager(true);
                $interventionManager->save($intervention);

                $mailerProvider->sendSignalementEstimationAccepted($intervention->getEntreprise()->getUser()->getEmail(), $intervention->getSignalement());

                // On refuse les autres estimations en attente
                $interventionsToAnswer = $interventionRepository->findInterventionsWithMissingAnswerFromUsager($intervention->getSignalement());
                foreach ($interventionsToAnswer as $interventionToAnswer) {
                    $interventionToAnswer->setChoiceByUsagerAt(new \DateTimeImmutable());
                    $interventionToAnswer->setAcceptedByUsager(false);
                    $interventionManager->save($interventionToAnswer);
                    $mailerProvider->sendSignalementEstimationRefused($interventionToAnswer->getEntreprise()->getUser()->getEmail(), $intervention->getSignalement());
                    $eventDispatcher->dispatch(
                        new InterventionUsagerRefusedEvent(
                            $interventionToAnswer
                        ),
                        InterventionUsagerRefusedEvent::NAME
                    );
                }

                $eventDispatcher->dispatch(
                    new InterventionUsagerAcceptedEvent(
                        $intervention
                    ),
                    InterventionUsagerAcceptedEvent::NAME
                );
            } elseif ('refuse' == $request->get('action')) {
                $this->addFlash('success', 'L\'estimation a bien été refusée');
                $intervention->setChoiceByUsagerAt(new \DateTimeImmutable());
                $intervention->setAcceptedByUsager(false);
                $interventionManager->save($intervention);

                $eventDispatcher->dispatch(
                    new InterventionUsagerRefusedEvent(
                        $intervention
                    ),
                    InterventionUsagerRefusedEvent::NAME
                );

                $mailerProvider->sendSignalementEstimationRefused($intervention->getEntreprise()->getUser()->getEmail(), $intervention->getSignalement());
            }
        }

        return $this->redirectToRoute('app_suivi_usager_view', ['uuidPublic' => $intervention->getSignalement()->getUuidPublic()]);
    }

    #[Route(
        '/signalements/{signalement_uuid}/messages-thread/{thread_uuid}',
        name: 'app_suivi_usager_view_messages_thread'
    )]
    #[ParamConverter('signalement', options: ['mapping' => ['signalement_uuid' => 'uuid']])]
    #[ParamConverter('messageThread', options: ['mapping' => ['thread_uuid' => 'uuid']])]
    public function displayThreadMessages(Request $request, Signalement $signalement, MessageThread $messageThread): Response
    {
        return $this->render('front_suivi_usager/messages_thread.html.twig', [
            'signalement' => $signalement,
            'entreprise_name' => $messageThread->getEntreprise()->getNom(),
            'messages' => $messageThread->getMessages(),
            'messages_thread_uuid' => $messageThread->getUuid(),
        ]);
    }
}
