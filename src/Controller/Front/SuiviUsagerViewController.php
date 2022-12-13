<?php

namespace App\Controller\Front;

use App\Entity\Enum\InfestationLevel;
use App\Entity\Intervention;
use App\Entity\Signalement;
use App\Manager\InterventionManager;
use App\Manager\SignalementManager;
use App\Repository\InterventionRepository;
use App\Service\Mailer\MailerProvider;
use App\Service\Signalement\EventsProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SuiviUsagerViewController extends AbstractController
{
    #[Route('/signalements/{uuid}', name: 'app_suivi_usager_view')]
    public function suivi_usager(Signalement $signalement, InterventionRepository $interventionRepository): Response
    {
        $eventsProvider = new EventsProvider($signalement, $this->getParameter('base_url').'/build/'.$this->getParameter('doc_autotraitement'));
        $events = $eventsProvider->getEvents();
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
        SignalementManager $signalementManager
        ): Response {
        if ($this->isCsrfTokenValid('signalement_switch_pro', $request->get('_csrf_token'))) {
            $this->addFlash('success', 'Votre signalement est transféré ! Les entreprises vont vous contacter au plus vite !');
            $signalement->setAutotraitement(false);
            $signalement->setSwitchedTraitementAt(new \DateTimeImmutable());
            $signalementManager->save($signalement);

            // TODO : envoyer un message aux entreprises concernées ? Non spécifié
        }

        return $this->redirectToRoute('app_suivi_usager_view', ['uuid' => $signalement->getUuid()]);
    }

    #[Route('/signalements/{uuid}/basculer-auto', name: 'app_signalement_switch_auto', methods: 'POST')]
    public function signalement_bascule_auto(
        Request $request,
        Signalement $signalement,
        SignalementManager $signalementManager
        ): Response {
        if ($this->isCsrfTokenValid('signalement_switch_auto', $request->get('_csrf_token'))) {
            $this->addFlash('success', 'Votre choix a été enregistré. Vous pouvez consulter le protocole d\'auto-traitement.');
            $signalement->setAutotraitement(true);
            $signalement->setReminderAutotraitementAt(null);
            $signalement->setSwitchedTraitementAt(new \DateTimeImmutable());
            $signalementManager->save($signalement);
        }

        return $this->redirectToRoute('app_suivi_usager_view', ['uuid' => $signalement->getUuid()]);
    }

    #[Route('/signalements/{uuid}/basculer-autotraitement', name: 'app_signalement_switch_autotraitement', methods: 'POST')]
    public function signalement_bascule_autotraitement(
        Request $request,
        Signalement $signalement,
        SignalementManager $signalementManager,
        MailerProvider $mailerProvider,
        ): Response {
        if ($this->isCsrfTokenValid('signalement_switch_autotraitement', $request->get('_csrf_token'))) {
            $this->addFlash('success', 'Le protocole vous a été transmis !');
            $signalement->setAutotraitement(true);
            $signalement->setSwitchedTraitementAt(new \DateTimeImmutable());
            $signalementManager->save($signalement);

            $linkToPdf = $this->getParameter('base_url').'/build/'.$this->getParameter('doc_autotraitement');
            $mailerProvider->sendSignalementValidationWithAutotraitement($signalement, $linkToPdf);
        }

        return $this->redirectToRoute('app_suivi_usager_view', ['uuid' => $signalement->getUuid()]);
    }

    #[Route('/signalements/{uuid}/resoudre', name: 'app_signalement_resolve', methods: 'POST')]
    public function signalement_resolu(
        Request $request,
        Signalement $signalement,
        SignalementManager $signalementManager,
        InterventionRepository $interventionRepository,
        MailerProvider $mailerProvider,
        ): Response {
        if ($this->isCsrfTokenValid('signalement_resolve', $request->get('_csrf_token'))) {
            $this->addFlash('success', 'Votre procédure est terminée !');
            $signalement->setResolvedAt(new \DateTimeImmutable());
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
        }

        return $this->redirectToRoute('app_suivi_usager_view', ['uuid' => $signalement->getUuid()]);
    }

    #[Route('/signalements/{uuid}/notification-toujours-punaises', name: 'app_signalement_confirm_toujours_punaises', methods: 'POST')]
    public function signalement_confirm_toujours_punaises(
        Request $request,
        Signalement $signalement,
        MailerProvider $mailerProvider,
        ): Response {
        if ($this->isCsrfTokenValid('signalement_confirm_toujours_punaises', $request->get('_csrf_token'))) {
            $this->addFlash('success', 'Stop Punaises a été prévenu de votre retour.');
            $mailerProvider->sendAdminToujoursPunaises($this->getParameter('admin_email'), $signalement);
        }

        return $this->redirectToRoute('app_suivi_usager_view', ['uuid' => $signalement->getUuid()]);
    }

    #[Route('/signalements/{uuid}/stop', name: 'app_signalement_stop', methods: 'POST')]
    public function signalement_stop(
        Request $request,
        Signalement $signalement,
        SignalementManager $signalementManager,
        InterventionRepository $interventionRepository,
        MailerProvider $mailerProvider,
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
        }

        return $this->redirectToRoute('app_suivi_usager_view', ['uuid' => $signalement->getUuid()]);
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

        return $this->redirectToRoute('app_suivi_usager_view', ['uuid' => $intervention->getSignalement()->getUuid()]);
    }
}
