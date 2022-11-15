<?php

namespace App\Controller\Front;

use App\Entity\Enum\Declarant;
use App\Entity\Signalement;
use App\Exception\File\MaxUploadSizeExceededException;
use App\Form\SignalementFrontType;
use App\Manager\SignalementManager;
use App\Service\Mailer\MailerProviderInterface;
use App\Service\Signalement\ReferenceGenerator;
use App\Service\Upload\UploadHandlerService;
use DateTimeImmutable;
use League\Flysystem\FilesystemException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class SignalementController extends AbstractController
{
    #[Route('/signalement', name: 'app_front_signalement')]
    public function signalement(Request $request): Response
    {
        $signalement = new Signalement();
        $form = $this->createForm(SignalementFrontType::class, $signalement);
        $codePostal = $request->get('code-postal');

        return $this->render('front_signalement/index.html.twig', [
            'form' => $form->createView(),
            'code_postal' => $codePostal,
        ]);
    }

    #[Route('/signalement/ajout', name: 'app_front_signalement_add')]
    public function save(
        Request $request,
        SignalementManager $signalementManager,
        ReferenceGenerator $referenceGenerator,
        UploadHandlerService $uploadHandlerService,
        SluggerInterface $slugger,
        LoggerInterface $logger,
        MailerProviderInterface $mailerProvider,
        ): Response {
        $signalement = new Signalement();
        $form = $this->createForm(SignalementFrontType::class, $signalement);
        $form->handleRequest($request);

        $submittedToken = $request->request->get('_token');
        if ($this->isCsrfTokenValid('front-add-signalement', $submittedToken)) {
            $signalement->setReference($referenceGenerator->generate());
            $signalement->setDeclarant(Declarant::DECLARANT_OCCUPANT);
            $filesToSave = $this->getUploadedFiles($request, $uploadHandlerService, $slugger, $logger);
            $signalement->setPhotos($filesToSave);

            $data = $request->get('signalement_front');
            $dejectionsDetails = $this->getDejectionDetails($data);
            if (!empty($dejectionsDetails)) {
                $signalement->setDejectionsDetails($dejectionsDetails);
            }
            $oeufsEtLarvesDetails = $this->getOeufsEtLarvesDetails($data);
            if (!empty($oeufsEtLarvesDetails)) {
                $signalement->setOeufsEtLarvesDetails($oeufsEtLarvesDetails);
            }
            $punaisesDetails = $this->getPunaisesDetails($data);
            if (!empty($punaisesDetails)) {
                $signalement->setPunaisesDetails($punaisesDetails);
            }

            $signalementManager->save($signalement);

            if ($signalement->isAutotraitement()) {
                $mailerProvider->sendSignalementValidationWithAutotraitement($signalement);
            } else {
                $mailerProvider->sendSignalementValidationWithPro($signalement);
            }

            $this->addFlash('success', 'Le signalement a bien été enregistré.');

            return $this->json(['response' => 'success']);
        }

        return $this->json(['response' => 'error', 'errors' => $form->getErrors(true)], Response::HTTP_BAD_REQUEST);
    }

    private function getUploadedFiles(
        Request $request,
        UploadHandlerService $uploadHandlerService,
        SluggerInterface $slugger,
        LoggerInterface $logger,
        ): array {
        $filesPosted = $request->files->get('file-upload');
        $filesToSave = [];
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

        return $filesToSave;
    }

    private function getDejectionDetails(array $data): array
    {
        $dejectionsDetails = [];
        if (!empty($data['dejectionsTrouvees'])) {
            $dejectionsDetails = [
                'dejectionsTrouvees' => $data['dejectionsTrouvees'],
            ];
            if ('true' == $data['dejectionsTrouvees']) {
                $dejectionsDetails['dejectionsNombrePiecesConcernees'] = $data['dejectionsNombrePiecesConcernees'];
                $dejectionsDetails['dejectionsFaciliteDetections'] = $data['dejectionsFaciliteDetections'];
                $dejectionsDetails['dejectionsLieuxObservations'] = $data['dejectionsLieuxObservations'];
            }
        }

        return $dejectionsDetails;
    }

    private function getOeufsEtLarvesDetails(array $data): array
    {
        $oeufsEtLarvesDetails = [];
        if (!empty($data['oeufsEtLarvesTrouves'])) {
            $oeufsEtLarvesDetails = [
                'oeufsEtLarvesTrouves' => $data['oeufsEtLarvesTrouves'],
            ];
            if ('true' == $data['oeufsEtLarvesTrouves']) {
                $oeufsEtLarvesDetails['oeufsEtLarvesNombrePiecesConcernees'] = $data['oeufsEtLarvesNombrePiecesConcernees'];
                $oeufsEtLarvesDetails['oeufsEtLarvesFaciliteDetections'] = $data['oeufsEtLarvesFaciliteDetections'];
                $oeufsEtLarvesDetails['oeufsEtLarvesLieuxObservations'] = $data['oeufsEtLarvesLieuxObservations'];
            }
        }

        return $oeufsEtLarvesDetails;
    }

    private function getPunaisesDetails(array $data): array
    {
        $punaisesDetails = [];
        if (!empty($data['punaisesTrouvees'])) {
            $punaisesDetails = [
                'punaisesTrouvees' => $data['punaisesTrouvees'],
            ];
            if ('true' == $data['punaisesTrouvees']) {
                $punaisesDetails['punaisesNombrePiecesConcernees'] = $data['punaisesNombrePiecesConcernees'];
                $punaisesDetails['punaisesFaciliteDetections'] = $data['punaisesFaciliteDetections'];
                $punaisesDetails['punaisesLieuxObservations'] = $data['punaisesLieuxObservations'];
            }
        }

        return $punaisesDetails;
    }
}
