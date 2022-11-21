<?php

namespace App\Controller\Front;

use App\Entity\Enum\Declarant;
use App\Entity\Signalement;
use App\Form\SignalementFrontType;
use App\Manager\SignalementManager;
use App\Repository\TerritoireRepository;
use App\Service\Mailer\MailerProvider;
use App\Service\Signalement\ReferenceGenerator;
use App\Service\Upload\UploadHandlerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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

    #[Route('/signalement/ajout', name: 'app_front_signalement_add', methods: ['POST'])]
    public function save(
        Request $request,
        SignalementManager $signalementManager,
        ReferenceGenerator $referenceGenerator,
        UploadHandlerService $uploadHandlerService,
        TerritoireRepository $territoireRepository,
        MailerProvider $mailerProvider,
        ): Response {
        $signalement = new Signalement();
        $form = $this->createForm(SignalementFrontType::class, $signalement);
        $form->handleRequest($request);

        $submittedToken = $request->request->get('_csrf_token');
        if ($form->isValid() && $this->isCsrfTokenValid('front-add-signalement', $submittedToken)) {
            $signalement->setReference($referenceGenerator->generate());
            $signalement->setDeclarant(Declarant::DECLARANT_OCCUPANT);

            $filesPosted = $request->files->get('file-upload');
            $filesToSave = $uploadHandlerService->handleUploadFilesRequest($filesPosted);
            $signalement->setPhotos($filesToSave);

            $zipCode = substr($signalement->getCodePostal(), 0, 2);
            if ('97' == $zipCode) {
                $zipCode = substr($signalement->getCodePostal(), 0, 3);
            }
            $territoire = $territoireRepository->findOneBy(['zip' => $zipCode]);
            $signalement->setTerritoire($territoire);

            $signalementManager->save($signalement);

            if ($signalement->isAutotraitement()) {
                $linkToPdf = $this->getParameter('doc_autotraitement');
                $mailerProvider->sendSignalementValidationWithAutotraitement($signalement, $linkToPdf);
            } else {
                $mailerProvider->sendSignalementValidationWithPro($signalement);
            }

            $this->addFlash('success', 'Le signalement a bien été enregistré.');

            return $this->json(['response' => 'success']);
        }

        return $this->json(['response' => 'error', 'errors' => $form->getErrors(true)], Response::HTTP_BAD_REQUEST);
    }
}
