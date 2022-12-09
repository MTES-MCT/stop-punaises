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
        $eventsProvider = new EventsProvider($signalement, $this->getParameter('doc_autotraitement'));
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

        return $this->render('front_suivi_usager/index.html.twig', [
            'signalement' => $signalement,
            'link_pdf' => $signalement->isAutotraitement() ? $this->getParameter('doc_autotraitement') : $this->getParameter('doc_domicile'),
            'niveau_infestation' => InfestationLevel::from($signalement->getNiveauInfestation())->label(),
            'events' => $events,
            'accepted_interventions' => $acceptedInterventions,
            'accepted_estimations' => $interventionsAcceptedByUsager,
            'interventions_to_answer' => $interventionsToAnswer,
            'is_last_intervention_to_answer' => 1 === \count($interventionsWithoutChoiceUsager),
            'intervention_accepted_by_usager' => $interventionAcceptedByUsager,
        ]);
    }

    #[Route('/signalements/{uuid}/basculer-pro', name: 'app_signalement_switch_pro')]
    public function signalement_bascule_pro(
        Request $request,
        Signalement $signalement,
        SignalementManager $signalementManager
        ): Response {
        if ($this->isCsrfTokenValid('signalement_switch_pro', $request->get('_csrf_token'))) {
            $this->addFlash('success', 'Votre signalement est transféré ! Les entreprises vont vous contacter au plus vite !');
            $signalement->setAutotraitement(false);
            $signalement->setSwitchedTraitementAt(new \DateTimeImmutable());
            $signalement->setUpdatedAtValue();
            $signalementManager->save($signalement);
        }

        return $this->redirectToRoute('app_suivi_usager_view', ['uuid' => $signalement->getUuid()]);
    }

    #[Route('/signalements/{uuid}/basculer-autotraitement', name: 'app_signalement_switch_autotraitement')]
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
            $signalement->setUpdatedAtValue();
            $signalementManager->save($signalement);

            $linkToPdf = $this->getParameter('doc_autotraitement');
            $mailerProvider->sendSignalementValidationWithAutotraitement($signalement, $linkToPdf);
        }

        return $this->redirectToRoute('app_suivi_usager_view', ['uuid' => $signalement->getUuid()]);
    }

    #[Route('/signalements/{uuid}/resoudre', name: 'app_signalement_resolve')]
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
            $signalement->setUpdatedAtValue();
            $signalementManager->save($signalement);

            $interventionsAcceptedByUsager = $interventionRepository->findBy([
                'signalement' => $signalement,
                'acceptedByUsager' => true,
            ]);
            foreach ($interventionsAcceptedByUsager as $intervention) {
                $mailerProvider->sendSignalementTraitementResolvedForPro($intervention->getEntreprise()->getEmail(), $intervention->getSignalement());
            }
        }

        return $this->redirectToRoute('app_suivi_usager_view', ['uuid' => $signalement->getUuid()]);
    }

    #[Route('/signalements/{uuid}/stop', name: 'app_signalement_stop')]
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
            $signalement->setUpdatedAtValue();
            $signalementManager->save($signalement);

            // Notice for entreprises which are currently following the signalement
            $acceptedInterventions = $interventionRepository->findBy([
                'signalement' => $signalement,
                'accepted' => true,
            ]);
            foreach ($acceptedInterventions as $intervention) {
                if (!$intervention->getChoiceByUsagerAt() || $intervention->isAcceptedByUsager()) {
                    $mailerProvider->sendSignalementClosed($intervention->getEntreprise()->getEmail(), $intervention->getSignalement());
                }
            }
        }

        return $this->redirectToRoute('app_suivi_usager_view', ['uuid' => $signalement->getUuid()]);
    }

    #[Route('/interventions/{id}/choix', name: 'app_signalement_estimation_choice')]
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

                $mailerProvider->sendSignalementEstimationAccepted($intervention->getEntreprise()->getEmail(), $intervention->getSignalement());

                // Refuser les autres estimations en attente
                $interventionsToAnswer = $interventionRepository->findInterventionsWithMissingAnswerFromUsager($intervention->getSignalement());
                foreach ($interventionsToAnswer as $interventionToAnswer) {
                    $interventionToAnswer->setChoiceByUsagerAt(new \DateTimeImmutable());
                    $interventionToAnswer->setAcceptedByUsager(false);
                    $interventionManager->save($interventionToAnswer);
                    $mailerProvider->sendSignalementEstimationRefused($interventionToAnswer->getEntreprise()->getEmail(), $intervention->getSignalement());
                }
            } elseif ('refuse' == $request->get('action')) {
                $this->addFlash('success', 'L\'estimation a bien été refusée');
                $intervention->setChoiceByUsagerAt(new \DateTimeImmutable());
                $intervention->setAcceptedByUsager(false);
                $interventionManager->save($intervention);

                $mailerProvider->sendSignalementEstimationRefused($intervention->getEntreprise()->getEmail(), $intervention->getSignalement());
            }
        }

        return $this->redirectToRoute('app_suivi_usager_view', ['uuid' => $intervention->getSignalement()->getUuid()]);
    }
}
