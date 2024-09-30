<?php

namespace App\Controller\Front;

use App\Entity\Signalement;
use App\Form\SignalementErpType;
use App\Manager\SignalementManager;
use App\Service\Mailer\MailerProvider;
use App\Service\Signalement\GeolocateService;
use App\Service\Upload\UploadHandlerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SignalementErpController extends AbstractController
{
    #[Route(
        '/signalement/erp',
        name: 'app_front_signalement_erp',
        defaults: ['show_sitemap' => false]
    )]
    public function index(ParameterBagInterface $parameterBag): Response
    {
        if (!$parameterBag->get('feature_three_forms')) {
            return $this->redirectToRoute('home');
        }

        $form = $this->createForm(SignalementErpType::class, new Signalement());

        return $this->render('front_signalement_erp/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/signalement/erp/ajout', name: 'app_front_signalement_erp_save', methods: ['POST'])]
    public function save(
        Request $request,
        SignalementManager $signalementManager,
        UploadHandlerService $uploadHandlerService,
        MailerProvider $mailerProvider,
        GeolocateService $geolocateService,
        ParameterBagInterface $parameterBag,
    ): Response {
        if (!$parameterBag->get('feature_three_forms')) {
            return $this->redirectToRoute('home');
        }
        $signalement = new Signalement();
        $form = $this->createForm(SignalementErpType::class, $signalement);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $files = $uploadHandlerService->handleUploadFilesRequest($request->files->get('file-upload'));
            $signalement->setPhotos($files);
            $geolocateService->geolocate($signalement);
            $signalementManager->save($signalement);
            $mailerProvider->sendSignalementValidationWithConseilsEviterPunaises($signalement);

            return $this->json(['response' => 'success']);
        }

        $errorMessage = [];
        /** @var FormError $error */
        foreach ($form->getErrors(true) as $error) {
            $errorMessage['signalement_front['.$error->getOrigin()->getName().']'] = $error->getMessage();
        }

        return $this->json(['error' => $errorMessage], Response::HTTP_BAD_REQUEST);
    }
}
