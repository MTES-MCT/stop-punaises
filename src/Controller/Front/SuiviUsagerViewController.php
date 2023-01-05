<?php

namespace App\Controller\Front;

use App\Entity\Enum\InfestationLevel;
use App\Entity\Event;
use App\Entity\Intervention;
use App\Entity\MessageThread;
use App\Entity\Signalement;
use App\Manager\EventManager;
use App\Manager\InterventionManager;
use App\Manager\SignalementManager;
use App\Repository\EventRepository;
use App\Repository\InterventionRepository;
use App\Service\Mailer\MailerProvider;
use App\Service\Signalement\EventsProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        $eventsProvider = new EventsProvider($signalement, $this->getParameter('base_url').'/build/'.$this->getParameter('doc_autotraitement'));
        $events = $eventsProvider->getEvents();
        $messageEvents = $eventRepository->findMessageEvents(
            $signalement->getUuid(),
            $signalement->getEmailOccupant()
        );
        $usagerEvents = $eventRepository->findUsagerEvents(
            signalementUuid: $signalement->getUuid(),
        );

        $events = array_merge($events, $messageEvents, $usagerEvents);
        usort($events, fn ($a, $b) => $a['date'] > $b['date'] ? -1 : 1);

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

        return $this->render('front_suivi_usager/index.html.twig', [
            'signalement' => $signalement,
            'link_pdf' => $this->getParameter('base_url').'/build/'.$docFile,
            'niveau_infestation' => InfestationLevel::from($signalement->getNiveauInfestation())->label(),
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
        EventManager $eventManager,
        ): Response {
        if ($this->isCsrfTokenValid('signalement_switch_pro', $request->get('_csrf_token'))) {
            $this->addFlash('success', 'Votre signalement est transféré ! Les entreprises vont vous contacter au plus vite !');
            $signalement->setAutotraitement(false);
            $signalement->setSwitchedTraitementAt(new \DateTimeImmutable());
            $signalementManager->save($signalement);

            // TODO : envoyer un message aux entreprises concernées ? Non spécifié

            $eventManager->createEventSwitchTraitement(
                signalement: $signalement,
                description: 'Votre signalement a été transmis aux entreprises labellisées. Elles vous contacteront au plus vite.',
                recipient: $signalement->getEmailOccupant(),
                userId: null,
            );
            $eventManager->createEventSwitchTraitement(
                signalement: $signalement,
                description: 'Le signalement a été passé en traitement professionnel.',
                recipient: null,
                userId: Event::USER_ALL,
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
        EventManager $eventManager,
        ): Response {
        if ($this->isCsrfTokenValid('signalement_switch_autotraitement', $request->get('_csrf_token'))) {
            $this->addFlash('success', 'Votre choix a été enregistré. Vous pouvez consulter le protocole d\'auto-traitement.');
            $signalement->setAutotraitement(true);
            $signalement->setReminderAutotraitementAt(null);
            $signalement->setSwitchedTraitementAt(new \DateTimeImmutable());
            $signalementManager->save($signalement);

            $linkToPdf = $this->getParameter('base_url').'/build/'.$this->getParameter('doc_autotraitement');
            $mailerProvider->sendSignalementValidationWithAutotraitement($signalement, $linkToPdf);

            $eventManager->createEventSwitchTraitement(
                signalement: $signalement,
                description: 'Votre signalement a été passé en auto-traitement.',
                recipient: $signalement->getEmailOccupant(),
                userId: null,
            );
            $eventManager->createEventSwitchTraitement(
                signalement: $signalement,
                description: 'Le signalement a été passé en auto-traitement.',
                recipient: null,
                userId: Event::USER_ALL,
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
        EventManager $eventManager,
        ): Response {
        if ($this->isCsrfTokenValid('signalement_resolve', $request->get('_csrf_token'))) {
            $this->addFlash('success', 'Votre procédure est terminée !');
            $signalement->setResolvedAt(new \DateTimeImmutable());
            $signalement->setUuidPublic(uniqid());
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

            $eventManager->createEventResolveSignalement(
                signalement: $signalement,
                description: 'L\'usager a indiqué que l\'infestation est résolue.',
                recipient: null,
                userId: Event::USER_ALL,
            );
            $eventManager->createEventResolveSignalement(
                signalement: $signalement,
                description: 'Vous avez résolu votre problème ! Merci d\'avoir utilisé Stop Punaises.',
                recipient: $signalement->getEmailOccupant(),
                userId: null,
            );
        }

        return $this->redirectToRoute('app_suivi_usager_view', ['uuidPublic' => $signalement->getUuidPublic()]);
    }

    #[Route('/signalements/{uuid}/notification-toujours-punaises', name: 'app_signalement_confirm_toujours_punaises', methods: 'POST')]
    public function signalement_confirm_toujours_punaises(
        Request $request,
        Signalement $signalement,
        MailerProvider $mailerProvider,
        EventManager $eventManager,
        ): Response {
        if ($this->isCsrfTokenValid('signalement_confirm_toujours_punaises', $request->get('_csrf_token'))) {
            $this->addFlash('success', 'Stop Punaises a été prévenu de votre retour.');
            $mailerProvider->sendAdminToujoursPunaises($this->getParameter('admin_email'), $signalement);
            // Event for usager
            $eventManager->createEventAdminNotice(
                signalement: $signalement,
                description: 'Vous avez indiqué que le problème de punaises n\'est pas résolu. L\'administrateur va vous contacter.',
                recipient: $signalement->getEmailOccupant(),
                userId: null,
            );
            // Event for admin
            $eventManager->createEventAdminNotice(
                signalement: $signalement,
                description: 'L\'usager a indiqué que le problème de punaises n\'est pas résolu. L\'administrateur va le contacter.',
                recipient: null,
                userId: Event::USER_ADMIN,
            );
            // Event for entreprise which resolved
            $userId = null;
            $interventions = $signalement->getInterventions();
            foreach ($interventions as $intervention) {
                if ($intervention->isAcceptedByUsager() && $intervention->getResolvedByEntrepriseAt()) {
                    $userId = $intervention->getEntreprise()->getId();
                }
            }
            $eventManager->createEventAdminNotice(
                signalement: $signalement,
                description: 'L\'usager a indiqué que le problème de punaises n\'est pas résolu. L\'administrateur va le contacter.',
                recipient: null,
                userId: $userId,
            );
        }

        return $this->redirectToRoute('app_suivi_usager_view', ['uuidPublic' => $signalement->getUuidPublic()]);
    }

    #[Route('/signalements/{uuid}/stop', name: 'app_signalement_stop', methods: 'POST')]
    public function signalement_stop(
        Request $request,
        Signalement $signalement,
        SignalementManager $signalementManager,
        InterventionRepository $interventionRepository,
        MailerProvider $mailerProvider,
        EventManager $eventManager,
        ): Response {
        if ($this->isCsrfTokenValid('signalement_stop', $request->get('_csrf_token'))) {
            $this->addFlash('success', 'Votre procédure est terminée !');
            $signalement->setClosedAt(new \DateTimeImmutable());
            $signalementManager->save($signalement);

            // Notice for entreprises which are currently following the signalement
            $acceptedInterventions = $interventionRepository->findBy([
                'signalement' => $signalement,
                'accepted' => true,
            ]);
            foreach ($acceptedInterventions as $intervention) {
                if (!$intervention->getChoiceByUsagerAt() || $intervention->isAcceptedByUsager()) {
                    $mailerProvider->sendSignalementClosed($intervention->getEntreprise()->getUser()->getEmail(), $intervention->getSignalement());
                }
            }

            $eventManager->createEventCloseSignalement(
                signalement: $signalement,
                description: 'L\'usager a mis fin à la procédure',
                recipient: null,
                userId: Event::USER_ALL,
            );
            $eventManager->createEventCloseSignalement(
                signalement: $signalement,
                description: 'Vous avez mis fin à la procédure. Merci d\'avoir utilisé Stop Punaises.',
                recipient: $signalement->getEmailOccupant(),
                userId: null,
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
        ): Response {
        if ($this->isCsrfTokenValid('signalement_estimation_choice', $request->get('_csrf_token'))) {
            if ('accept' == $request->get('action')) {
                $this->addFlash('success', 'L\'estimation a bien été acceptée');
                $intervention->setChoiceByUsagerAt(new \DateTimeImmutable());
                $intervention->setAcceptedByUsager(true);
                $interventionManager->save($intervention);

                $mailerProvider->sendSignalementEstimationAccepted($intervention->getEntreprise()->getUser()->getEmail(), $intervention->getSignalement());

                // Refuser les autres estimations en attente
                $interventionsToAnswer = $interventionRepository->findInterventionsWithMissingAnswerFromUsager($intervention->getSignalement());
                foreach ($interventionsToAnswer as $interventionToAnswer) {
                    $interventionToAnswer->setChoiceByUsagerAt(new \DateTimeImmutable());
                    $interventionToAnswer->setAcceptedByUsager(false);
                    $interventionManager->save($interventionToAnswer);
                    $mailerProvider->sendSignalementEstimationRefused($interventionToAnswer->getEntreprise()->getUser()->getEmail(), $intervention->getSignalement());
                }
            } elseif ('refuse' == $request->get('action')) {
                $this->addFlash('success', 'L\'estimation a bien été refusée');
                $intervention->setChoiceByUsagerAt(new \DateTimeImmutable());
                $intervention->setAcceptedByUsager(false);
                $interventionManager->save($intervention);

                $mailerProvider->sendSignalementEstimationRefused($intervention->getEntreprise()->getUser()->getEmail(), $intervention->getSignalement());
            }
        }

        return $this->redirectToRoute('app_suivi_usager_view', ['uuidPublic' => $intervention->getSignalement()->getUuidPublic()]);
    }

    #[Route('/signalements/{signalement_uuid}/messages-thread/{thread_uuid}',
        name: 'app_suivi_usager_view_messages_thread')]
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
