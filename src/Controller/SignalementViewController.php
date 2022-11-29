<?php

namespace App\Controller;

use App\Entity\Signalement;
use App\Manager\SignalementManager;
use App\Service\Upload\UploadHandlerService;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SignalementViewController extends AbstractController
{
    #[Route('/bo/signalements/{uuid}', name: 'app_signalement_view')]
    public function indexSignalement(Signalement $signalement): Response
    {
        if (!$signalement) {
            return $this->render('signalement_view/not-found.html.twig');
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

        return $this->render('signalement_view/signalement.html.twig', [
            'can_display_traitement' => false, // TODO
            'can_display_adresse' => false, // TODO
            'signalement' => $signalement,
            'photos' => $this->getPhotos($signalement),
        ]);
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
