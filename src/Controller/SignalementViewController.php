<?php

namespace App\Controller;

use App\Entity\Signalement;
use App\Exception\File\MaxUploadSizeExceededException;
use App\Manager\SignalementManager;
use App\Service\Upload\UploadHandlerService;
use DateTimeImmutable;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class SignalementViewController extends AbstractController
{
    #[Route('/bo/signalements/{uuid}', name: 'app_signalement_view')]
    public function index(Signalement $signalement): Response
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

        return $this->render('signalement_view/index.html.twig', [
            'signalement' => $signalement,
            'photos' => $photos,
        ]);
    }

    #[Route('/bo/signalements/{uuid}/ajouter-photos', name: 'app_add_photos')]
    public function addPhoto(
        Signalement $signalement,
        Request $request,
        UploadHandlerService $uploadHandlerService,
        SignalementManager $signalementManager,
        SluggerInterface $slugger,
        LoggerInterface $logger,
        ): Response {
        $filesPosted = $request->files->get('file-upload');
        $filesToSave = $signalement->getPhotos();
        if (null == $filesToSave) {
            $filesToSave = [];
        }
        if (isset($filesPosted) && \is_array($filesPosted)) {
            foreach ($filesPosted as $file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), \PATHINFO_FILENAME);
                $title = $originalFilename.'.'.$file->guessExtension();
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
                try {
                    $uploadHandlerService->uploadFromFile($file, $newFilename);
                } catch (FilesystemException $exception) {
                    $newFilename = '';
                    $logger->error($exception->getMessage());
                } catch (MaxUploadSizeExceededException $exception) {
                    $newFilename = '';
                    $logger->error($exception->getMessage());
                    $this->addFlash('error', $exception->getMessage());
                }
                if (!empty($newFilename)) {
                    array_push($filesToSave, [
                        'file' => $newFilename,
                        'title' => $title,
                        'date' => (new DateTimeImmutable())->format('d.m.Y'),
                    ]);
                }
            }
        }
        $signalement->setPhotos($filesToSave);
        $signalementManager->save($signalement);

        return $this->redirectToRoute('app_signalement_view', ['uuid' => $signalement->getUuid()]);
    }

    #[Route('/bo/signalements/{uuid}/{filename}/supprimer-photo', name: 'app_delete_photo')]
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

        return $this->redirectToRoute('app_signalement_view', ['uuid' => $signalement->getUuid()]);
    }
}
