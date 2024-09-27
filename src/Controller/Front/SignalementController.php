<?php

namespace App\Controller\Front;

use App\Entity\Enum\Declarant;
use App\Entity\Enum\SignalementType;
use App\Entity\Signalement;
use App\Entity\Territoire;
use App\Event\SignalementAddedEvent;
use App\Form\SignalementFrontType;
use App\Manager\SignalementManager;
use App\Repository\EntrepriseRepository;
use App\Repository\TerritoireRepository;
use App\Service\FormHelper;
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
    #[Route(
        '/signalement/logement',
        name: 'app_front_signalement_logement',
        defaults: ['show_sitemap' => true]
    )]
    public function signalementLogement(Request $request, TerritoireRepository $territoireRepository): Response
    {
        $signalement = new Signalement();
        $form = $this->createForm(SignalementFrontType::class, $signalement);
        $codePostal = $request->get('code-postal');

        $activeTerritoires = array_map(function ($codeDepartement) {
            if (Territoire::CORSE_DU_SUD_CODE_DEPARTMENT_2A === $codeDepartement
                || Territoire::HAUTE_CORSE_CODE_DEPARTMENT_2B === $codeDepartement) {
                return '20';
            }

            return $codeDepartement;
        }, $territoireRepository->findActiveTerritoires('t.zip'));

        return $this->render('front_signalement/index.html.twig', [
            'form' => $form->createView(),
            'code_postal' => $codePostal,
            'territoires_actives' => implode(',', array_unique($activeTerritoires)),
        ]);
    }

    #[Route('/signalement/logement/ajout', name: 'app_front_signalement_add', methods: ['POST'])]
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
        $form = $this->createForm(SignalementFrontType::class, $signalement);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $signalement
                ->setType(SignalementType::TYPE_LOGEMENT)
                ->setReference($referenceGenerator->generate())
                ->setDeclarant(Declarant::DECLARANT_OCCUPANT)
                ->updateUuidPublic();

            $filesPosted = $request->files->get('file-upload');
            $filesToSave = $uploadHandlerService->handleUploadFilesRequest($filesPosted);
            $signalement->setPhotos($filesToSave);
            $geolocateService->geolocate($signalement);

            $zipCode = $zipCodeService->getByCodePostal($signalement->getCodePostal());
            $territoire = $territoireRepository->findOneBy(['zip' => $zipCode]);
            if (!$territoire) {
                return $this->json(['response' => 'error', 'errors' => 'territoire introuvable sur le code postal '.$signalement->getCodePostal()], Response::HTTP_BAD_REQUEST);
            }
            $signalement->setTerritoire($territoire);
            $signalementManager->save($signalement);

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
                    $this->getParameter('base_url').'/build/'.$this->getParameter('doc_autotraitement'),
                    $this->getParameter('doc_autotraitement_size')
                ),
                SignalementAddedEvent::NAME
            );

            $this->addFlash('success', 'Le signalement a bien été enregistré.');

            return $this->json(['response' => 'success']);
        }
        $errors = FormHelper::getErrorsFromForm($form);

        return $this->json(['response' => 'error', 'errors' => $errors], Response::HTTP_BAD_REQUEST);
    }
}
