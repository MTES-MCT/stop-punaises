<?php

namespace App\Controller\Front;

use App\Entity\Enum\Declarant;
use App\Entity\Enum\SignalementType;
use App\Entity\Signalement;
use App\Event\SignalementAddedEvent;
use App\Form\SignalementFrontType;
use App\Manager\SignalementManager;
use App\Repository\EntrepriseRepository;
use App\Repository\TerritoireRepository;
use App\Service\Mailer\MailerProvider;
use App\Service\Signalement\GeolocateService;
use App\Service\Signalement\ReferenceGenerator;
use App\Service\Signalement\ZipCodeProvider;
use App\Service\Upload\UploadHandlerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SignalementController extends AbstractController
{
    #[Route('/signalement/logement', name: 'app_front_signalement_logement')]
    public function signalementLogement(Request $request): Response
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
        ZipCodeProvider $zipCodeService,
        EntrepriseRepository $entrepriseRepository,
        EventDispatcherInterface $eventDispatcher,
        GeolocateService $geolocateService,
    ): Response {
        $signalement = new Signalement();
        $form = $this->createForm(SignalementFrontType::class, $signalement, ['validation_groups' => 'front_add_signalement_logement']);
        $form->handleRequest($request);

        $submittedToken = $request->request->get('_csrf_token');
        if ($form->isValid() && $this->isCsrfTokenValid('front-add-signalement', $submittedToken)) {
            $signalement
                ->setType(SignalementType::TYPE_LOGEMENT)
                ->setReference($referenceGenerator->generate())
                ->setDeclarant(Declarant::DECLARANT_OCCUPANT)
                ->updateUuidPublic();

            $filesPosted = $request->files->get('file-upload');
            $filesToSave = $uploadHandlerService->handleUploadFilesRequest($filesPosted);
            $signalement->setPhotos($filesToSave);

            if ([] === $signalement->getGeoloc()) {
                $geolocateService->geolocate($signalement);
            }
            if (null !== $signalement->getCodePostal()) {
                $zipCode = $zipCodeService->getByCodePostal($signalement->getCodePostal());
                $territoire = $territoireRepository->findOneBy(['zip' => $zipCode]);
                $signalement->setTerritoire($territoire);

                $signalementManager->save($signalement);
            } else {
                return $this->json(['response' => 'error', 'errors' => 'code postal null'], Response::HTTP_BAD_REQUEST);
            }

            if ($signalement->isAutotraitement()) {
                if ($signalement->getTerritoire()->isActive()) {
                    $mailerProvider->sendSignalementValidationWithAutotraitement($signalement);
                } else {
                    $mailerProvider->sendSignalementValidationWithEntreprisesPubliques($signalement);
                }
            } else {
                $mailerProvider->sendSignalementValidationWithPro($signalement);

                $entreprises = $entrepriseRepository->findByTerritoire($signalement->getTerritoire());
                foreach ($entreprises as $entreprise) {
                    if ($entreprise->getUser() && $entreprise->getUser()->getEmail()) {
                        $mailerProvider->sendSignalementNewForPro($entreprise->getUser()->getEmail(), $signalement);
                    }
                }
            }

            $eventDispatcher->dispatch(
                new SignalementAddedEvent(
                    $signalement,
                    $this->getParameter('base_url').'/build/'.$this->getParameter('doc_autotraitement')
                ),
                SignalementAddedEvent::NAME
            );

            $this->addFlash('success', 'Le signalement a bien été enregistré.');

            return $this->json(['response' => 'success']);
        }

        return $this->json(['response' => 'error', 'errors' => $form->getErrors(true)], Response::HTTP_BAD_REQUEST);
    }
}
