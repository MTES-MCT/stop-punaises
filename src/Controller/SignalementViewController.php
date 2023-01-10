<?php

namespace App\Controller;

use App\Entity\Entreprise;
use App\Entity\Intervention;
use App\Entity\MessageThread;
use App\Entity\Signalement;
use App\Entity\User;
use App\Event\InterventionEntrepriseAcceptedEvent;
use App\Event\InterventionEntrepriseAllRefusedEvent;
use App\Event\InterventionEntrepriseRefusedEvent;
use App\Event\InterventionEstimationSentEvent;
use App\Manager\EntrepriseManager;
use App\Manager\InterventionManager;
use App\Manager\SignalementManager;
use App\Repository\EventRepository;
use App\Repository\InterventionRepository;
use App\Repository\MessageThreadRepository;
use App\Service\Mailer\MailerProvider;
use App\Service\Upload\UploadHandlerService;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SignalementViewController extends AbstractController
{
    public function __construct(
        private MessageThreadRepository $messageThreadRepository,
        private EventRepository $eventRepository,
    ) {
    }

    #[Route('/bo/signalements/{uuid}', name: 'app_signalement_view')]
    public function indexSignalement(
        Signalement $signalement,
        InterventionRepository $interventionRepository,
        ): Response {
        if (!$signalement) {
            return $this->render('signalement_view/not-found.html.twig');
        }

        /** @var User $user */
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

        $interventionsAccepted = $interventionRepository->findBy([
            'signalement' => $signalement,
            'accepted' => true,
        ]);

        $estimations = $interventionRepository->findInterventionsWithEstimation($signalement);

        $acceptedEstimations = $interventionRepository->findBy([
            'signalement' => $signalement,
            'acceptedByUsager' => true,
        ]);

        return $this->render('signalement_view/signalement.html.twig', [
            'can_display_traitement' => null !== $signalement->getTypeIntervention(),
            'can_display_messages' => !$this->isGranted('ROLE_ADMIN') && $entrepriseIntervention && $entrepriseIntervention->isAccepted(),
            'can_display_adresse' => $this->isGranted('ROLE_ADMIN') || ($entrepriseIntervention && $entrepriseIntervention->isAcceptedByUsager()),
            'can_send_estimation' => !$this->isGranted('ROLE_ADMIN') && $entrepriseIntervention && $entrepriseIntervention->isAccepted(),
            'has_sent_estimation' => !$this->isGranted('ROLE_ADMIN') && $entrepriseIntervention && $entrepriseIntervention->getEstimationSentAt(),
            'has_other_entreprise' => \count($acceptedEstimations) > 0 && !$entrepriseIntervention,
            'accepted_interventions' => $interventionsAccepted,
            'accepted_estimations' => $acceptedEstimations,
            'estimations' => $estimations,
            'signalement' => $signalement,
            'messages' => $this->getMessages($signalement, $userEntreprise),
            'photos' => $this->getPhotos($signalement),
            'events' => $this->getEvents($signalement, $userEntreprise),
            'entrepriseIntervention' => $entrepriseIntervention,
            'entreprise' => $userEntreprise,
        ]);
    }

    #[Route('/bo/signalements/{uuid}/accept', name: 'app_signalement_intervention_accept', methods: 'POST')]
    public function signalementInterventionAccept(
        Request $request,
        Signalement $signalement,
        InterventionManager $interventionManager,
        EventDispatcherInterface $eventDispatcher,
        ): Response {
        if ($this->isCsrfTokenValid('signalement_intervention_accept', $request->get('_csrf_token'))) {
            $intervention = new Intervention();
            $intervention->setSignalement($signalement);
            /** @var User $user */
            $user = $this->getUser();
            $intervention->setEntreprise($user->getEntreprise());
            $intervention->setChoiceByEntrepriseAt(new DateTimeImmutable());
            $intervention->setAccepted(true);
            $interventionManager->save($intervention);
            $this->addFlash('success', 'Le signalement a bien été accepté');

            $eventDispatcher->dispatch(
                new InterventionEntrepriseAcceptedEvent(
                    $intervention,
                    $user->getId(),
                ),
                InterventionEntrepriseAcceptedEvent::NAME
            );
        }

        return $this->redirectToRoute('app_signalement_view', ['uuid' => $signalement->getUuid()]);
    }

    #[Route('/bo/signalements/{uuid}/refuse', name: 'app_signalement_intervention_refuse', methods: 'POST')]
    public function signalementInterventionRefuse(
        Request $request,
        Signalement $signalement,
        InterventionManager $interventionManager,
        MailerProvider $mailerProvider,
        EntrepriseManager $entrepriseManager,
        ValidatorInterface $validator,
        EventDispatcherInterface $eventDispatcher,
        ): Response {
        if ($this->isCsrfTokenValid('signalement_intervention_refuse', $request->get('_csrf_token'))) {
            $intervention = new Intervention();
            $intervention->setSignalement($signalement);
            /** @var User $user */
            $user = $this->getUser();
            $intervention->setEntreprise($user->getEntreprise());
            $intervention->setChoiceByEntrepriseAt(new DateTimeImmutable());
            $intervention->setAccepted(false);
            $intervention->setCommentaireRefus($request->get('commentaire'));
            $errors = $validator->validate($intervention);

            if (0 === $errors->count()) {
                $interventionManager->save($intervention);
                $this->addFlash('success', 'Le signalement a bien été refusé');

                $eventDispatcher->dispatch(
                    new InterventionEntrepriseRefusedEvent(
                        $intervention,
                        $user->getId(),
                    ),
                    InterventionEntrepriseRefusedEvent::NAME
                );

                // Check if entreprises are still available for this territoire
                // If not, contact user
                $remainingEntreprises = $entrepriseManager->isEntrepriseRemainingForSignalement($signalement);
                if (!$remainingEntreprises) {
                    $mailerProvider->sendSignalementWithNoMoreEntreprise($signalement);

                    $eventDispatcher->dispatch(
                        new InterventionEntrepriseAllRefusedEvent(
                            $intervention,
                        ),
                        InterventionEntrepriseAllRefusedEvent::NAME
                    );
                }
            } else {
                foreach ($errors as $error) {
                    $this->addFlash('error', (string) $error->getMessage());
                }
            }
        }

        return $this->redirectToRoute('app_signalement_view', ['uuid' => $signalement->getUuid()]);
    }

    #[Route('/bo/signalements/{uuid}/estimation', name: 'app_signalement_estimation_send', methods: 'POST')]
    public function signalementInterventionEstimation(
        Request $request,
        Signalement $signalement,
        InterventionManager $interventionManager,
        InterventionRepository $interventionRepository,
        MailerProvider $mailerProvider,
        EventDispatcherInterface $eventDispatcher,
        ): Response {
        if ($this->isCsrfTokenValid('signalement_estimation_send', $request->get('_csrf_token'))) {
            $montant = $request->get('montant');
            if (empty($montant) || !is_numeric($montant)) {
                $this->addFlash('error', 'Le montant saisi n\'est pas correct.');
            } else {
                /** @var User $user */
                $user = $this->getUser();
                $userEntreprise = $user?->getEntreprise();
                $intervention = $interventionRepository->findBySignalementAndEntreprise(
                    $signalement,
                    $userEntreprise
                );
                $intervention->setCommentaireEstimation($request->get('commentaire'));
                $intervention->setMontantEstimation($montant);
                $intervention->setEstimationSentAt(new DateTimeImmutable());
                $interventionManager->save($intervention);
                $this->addFlash('success', 'L\'estimation a bien été transmise.');

                $mailerProvider->sendSignalementNewEstimation($signalement, $intervention);

                $eventDispatcher->dispatch(
                    new InterventionEstimationSentEvent(
                        $intervention,
                        $user->getId(),
                    ),
                    InterventionEstimationSentEvent::NAME
                );
            }
        }

        return $this->redirectToRoute('app_signalement_view', ['uuid' => $signalement->getUuid()]);
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

    #[Route('/bo/historique/{uuid}/ajouter-photos', name: 'app_add_photos', methods: 'POST')]
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

    #[Route('/bo/historique/{uuid}/{filename}/supprimer-photo', name: 'app_delete_photo', methods: 'POST')]
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

    private function getMessages(Signalement $signalement, ?Entreprise $entreprise = null): ?Collection
    {
        /** @var MessageThread $messagesThread */
        $messagesThread = $this->messageThreadRepository->findOneBy([
            'signalement' => $signalement,
            'entreprise' => $entreprise,
        ]);

        return $messagesThread?->getMessages();
    }

    private function getEvents(
        Signalement $signalement,
        ?Entreprise $entreprise = null,
    ): array {
        $dbEvents = [];
        if ($this->isGranted('ROLE_ADMIN')) {
            $dbEvents = $this->eventRepository->findAdminEvents(
                signalementUuid: $signalement->getUuid(),
            );
        } else {
            $dbEvents = $this->eventRepository->findEntrepriseEvents(
                signalementUuid: $signalement->getUuid(),
                userId: $entreprise?->getUser()?->getId()
            );
        }

        return $dbEvents;
    }
}
