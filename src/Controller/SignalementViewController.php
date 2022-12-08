<?php

namespace App\Controller;

use App\Entity\Intervention;
use App\Entity\Signalement;
use App\Manager\InterventionManager;
use App\Manager\SignalementManager;
use App\Repository\InterventionRepository;
use App\Service\Signalement\EventsProvider;
use App\Service\Upload\UploadHandlerService;
use DateTimeImmutable;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SignalementViewController extends AbstractController
{
    #[Route('/bo/signalements/{uuid}', name: 'app_signalement_view')]
    public function indexSignalement(
        Signalement $signalement,
        InterventionRepository $interventionRepository,
        ): Response {
        if (!$signalement) {
            return $this->render('signalement_view/not-found.html.twig');
        }

        /* User $user */
        $user = $this->getUser();
        $userEntreprise = null;
        $entrepriseIntervention = null;
        if (!$this->isGranted('ROLE_ADMIN')) {
            $userEntreprise = $user->getEntreprise();
            $entrepriseIntervention = $interventionRepository->findBySignalementAndEntreprise(
                $signalement,
                $userEntreprise
            );
        }

        $eventsProvider = new EventsProvider(
            signalement: $signalement,
            pdfUrl: $this->getParameter('doc_autotraitement'),
            isAdmin: $this->isGranted('ROLE_ADMIN'),
            entreprise: $userEntreprise
        );

        $signalementPhotos = $signalement->getPhotos();
        $photos = [];
        foreach ($signalementPhotos as $signalementPhoto) {
            $photos[] = [
                'url' => $this->generateUrl(
                    'show_uploaded_file',
                    ['filename' => $signalementPhoto['file']]
                ),
                'file' => $signalementPhoto['file'],
                'title' => $signalementPhoto['title'],
                'date' => $signalementPhoto['date'],
            ];
        }

        $acceptedInterventions = $interventionRepository->findBy([
            'signalement' => $signalement,
            'accepted' => true,
        ]);

        return $this->render('signalement_view/signalement.html.twig', [
            'can_display_traitement' => false, // TODO
            'can_display_messages' => !$this->isGranted('ROLE_ADMIN') && $entrepriseIntervention && $entrepriseIntervention->isAccepted(),
            'can_display_adresse' => $this->isGranted('ROLE_ADMIN') || ($entrepriseIntervention && $entrepriseIntervention->isAcceptedByUsager()),
            'can_send_estimation' => !$this->isGranted('ROLE_ADMIN') && $entrepriseIntervention && $entrepriseIntervention->isAccepted(),
            'has_sent_estimation' => !$this->isGranted('ROLE_ADMIN') && $entrepriseIntervention && $entrepriseIntervention->getEstimationSentAt(),
            'accepted_interventions' => $acceptedInterventions,
            'signalement' => $signalement,
            'photos' => $this->getPhotos($signalement),
            'events' => $eventsProvider->getEvents(),
            'entrepriseIntervention' => $entrepriseIntervention,
            'entreprise' => $userEntreprise,
        ]);
    }

    #[Route('/bo/signalements/{uuid}/accept', name: 'app_signalement_intervention_accept')]
    public function signalementInterventionAccept(
        Request $request,
        Signalement $signalement,
        InterventionManager $interventionManager,
        ): Response {
        if ($this->isCsrfTokenValid('signalement_intervention_accept', $request->get('_csrf_token'))) {
            $intervention = new Intervention();
            $intervention->setSignalement($signalement);
            /* User $user */
            $user = $this->getUser();
            $intervention->setEntreprise($user->getEntreprise());
            $intervention->setChoiceByEntrepriseAt(new DateTimeImmutable());
            $intervention->setAccepted(true);
            $interventionManager->save($intervention);
            $this->addFlash('success', 'Le signalement a bien été accepté');
        }

        return $this->redirectToRoute('app_signalement_view', ['uuid' => $signalement->getUuid()]);
    }

    #[Route('/bo/signalements/{uuid}/refuse', name: 'app_signalement_intervention_refuse')]
    public function signalementInterventionRefuse(
        Request $request,
        Signalement $signalement,
        InterventionManager $interventionManager,
        ): Response {
        if ($this->isCsrfTokenValid('signalement_intervention_refuse', $request->get('_csrf_token'))) {
            $intervention = new Intervention();
            $intervention->setSignalement($signalement);
            /* User $user */
            $user = $this->getUser();
            $intervention->setEntreprise($user->getEntreprise());
            $intervention->setChoiceByEntrepriseAt(new DateTimeImmutable());
            $intervention->setAccepted(false);
            $commentaire = htmlentities($request->get('commentaire'));
            $intervention->setCommentaireRefus($commentaire);
            $interventionManager->save($intervention);
            $this->addFlash('success', 'Le signalement a bien été refusé');
            // TODO : vérifier si des entreprises sont encore dispos ou non pour envoyer un mail à l'usager si il n'y a plus d'entreprises
        }

        return $this->redirectToRoute('app_signalement_view', ['uuid' => $signalement->getUuid()]);
    }

    #[Route('/bo/signalements/{uuid}/estimation', name: 'app_signalement_estimation_send')]
    public function signalementInterventionEstimation(
        Request $request,
        Signalement $signalement,
        InterventionManager $interventionManager,
        InterventionRepository $interventionRepository,
        ): Response {
        if ($this->isCsrfTokenValid('signalement_estimation_send', $request->get('_csrf_token'))) {
            /* User $user */
            $user = $this->getUser();
            $userEntreprise = null;
            $userEntreprise = $user->getEntreprise();
            $intervention = $interventionRepository->findBySignalementAndEntreprise(
                $signalement,
                $userEntreprise
            );
            $commentaire = htmlentities($request->get('commentaire'));
            $intervention->setCommentaireEstimation($commentaire);
            $intervention->setMontantEstimation($request->get('montant'));
            $intervention->setEstimationSentAt(new DateTimeImmutable());
            $interventionManager->save($intervention);
            $this->addFlash('success', 'L\'estimation a bien été transmise.');
            // TODO : envoyer mail à l'usager
        }

        return $this->redirectToRoute('app_signalement_view', ['uuid' => $signalement->getUuid()]);
    }

    #[Route('/interventions/{id}/marquer-traite', name: 'app_signalement_intervention_resolved_by_entreprise')]
    public function intervention_resolved(
        Request $request,
        Intervention $intervention,
        InterventionManager $interventionManager,
        InterventionRepository $interventionRepository,
        ): Response {
        if ($this->isCsrfTokenValid('signalement_intervention_resolved_by_entreprise', $request->get('_csrf_token'))) {
            $this->addFlash('success', 'L\'estimation a bien été acceptée');
            $intervention->setChoiceByUsagerAt(new \DateTimeImmutable());
            $intervention->setAcceptedByUsager(true);
            $interventionManager->save($intervention);

            // Refuser les autres estimations en attente
            $interventionsToAnswer = $interventionRepository->findInterventionsWithMissingAnswerFromUsager($intervention->getSignalement());
            foreach ($interventionsToAnswer as $interventionToAnswer) {
                $interventionToAnswer->setChoiceByUsagerAt(new \DateTimeImmutable());
                $interventionToAnswer->setAcceptedByUsager(false);
                // TODO : setMotifRefusUsager : "choix d'une autre entreprise"
                $interventionManager->save($interventionToAnswer);
            }

            // TODO : envoi d'un mail à l'entreprise
            // TODO : envoi d'un mail aux autres entreprises
        }

        return $this->redirectToRoute('app_suivi_usager_view', ['uuid' => $intervention->getSignalement()->getUuid()]);
    }

    #[Route('/bo/historique/{uuid}', name: 'app_signalement_historique_view')]
    public function indexHistorique(Signalement $signalement): Response
    {
        if (!$signalement) {
            return $this->render('signalement_view/not-found.html.twig');
        }
        // Not the admin
        // and the signalement is linked to an Entreprise
        // but its Entreprise is different than the one from the user
        if (!$this->isGranted('ROLE_ADMIN')
            && null !== $signalement->getEntreprise()
            && $signalement->getEntreprise()->getId() !== $this->getUser()->getEntreprise()->getId()) {
            return $this->render('signalement_view/not-authorized.html.twig');
        }

        return $this->render('signalement_view/historique.html.twig', [
            'can_display_traitement' => true,
            'can_display_adresse' => true,
            'signalement' => $signalement,
            'photos' => $this->getPhotos($signalement),
        ]);
    }

    private function getPhotos(Signalement $signalement)
    {
        $signalementPhotos = $signalement->getPhotos();
        $photos = [];
        foreach ($signalementPhotos as $signalementPhoto) {
            $photos[] = [
                'url' => $this->generateUrl(
                    'show_uploaded_file',
                    ['filename' => $signalementPhoto['file']]
                ),
                'file' => $signalementPhoto['file'],
                'title' => $signalementPhoto['title'],
                'date' => $signalementPhoto['date'],
            ];
        }

        return $photos;
    }

    #[Route('/bo/historique/{uuid}/ajouter-photos', name: 'app_add_photos')]
    public function addPhoto(
        Signalement $signalement,
        Request $request,
        UploadHandlerService $uploadHandlerService,
        SignalementManager $signalementManager,
        ): Response {
        $filesPosted = $request->files->get('file-upload');
        $filesToSave = $signalement->getPhotos();
        if (null == $filesToSave) {
            $filesToSave = [];
        }
        $newFilesToSave = $uploadHandlerService->handleUploadFilesRequest($filesPosted);
        $filesToSave = array_merge($filesToSave, $newFilesToSave);
        $signalement->setPhotos($filesToSave);
        $signalementManager->save($signalement);

        return $this->redirectToRoute('app_signalement_historique_view', ['uuid' => $signalement->getUuid()]);
    }

    #[Route('/bo/historique/{uuid}/{filename}/supprimer-photo', name: 'app_delete_photo')]
    public function deletePhoto(
        Signalement $signalement,
        string $filename,
        Request $request,
        SignalementManager $signalementManager,
        FilesystemOperator $fileStorage): Response
    {
        $this->denyAccessUnlessGranted('FILE_DELETE', $signalement);
        if ($this->isCsrfTokenValid('signalement_delete_file_'.$signalement->getId(), $request->get('_csrf_token'))) {
            $filesToSave = $signalement->getPhotos();
            foreach ($filesToSave as $k => $v) {
                if ($filename === $v['file']) {
                    if ($fileStorage->fileExists($filename)) {
                        $fileStorage->delete($filename);
                    }
                    unset($filesToSave[$k]);
                }
            }
            $signalement->setPhotos($filesToSave);
            $signalementManager->save($signalement);
        }

        return $this->redirectToRoute('app_signalement_historique_view', ['uuid' => $signalement->getUuid()]);
    }
}
