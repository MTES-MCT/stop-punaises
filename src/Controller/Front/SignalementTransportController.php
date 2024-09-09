<?php

namespace App\Controller\Front;

use App\Entity\Signalement;
use App\Form\SignalementTransportType;
use App\Manager\SignalementManager;
use App\Service\Mailer\MailerProvider;
use App\Service\Upload\UploadHandlerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SignalementTransportController extends AbstractController
{
    #[Route(
        '/signalement/transport',
        name: 'app_front_signalement_transport',
        defaults: ['show_sitemap' => false]
    )]
    public function index(ParameterBagInterface $parameterBag): Response
    {
        if (!$parameterBag->get('feature_three_forms')) {
            return $this->redirectToRoute('home');
        }

        $form = $this->createForm(SignalementTransportType::class, new Signalement());

        return $this->render('front_signalement_transport/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/signalement/transport/ajout', name: 'app_front_signalement_transport_save', methods: ['POST'])]
    public function save(
        Request $request,
        SignalementManager $signalementManager,
        UploadHandlerService $uploadHandlerService,
        MailerProvider $mailerProvider,
    ): Response {
        $signalement = new Signalement();
        $form = $this->createForm(SignalementTransportType::class, $signalement);
        $form->handleRequest($request);

        if ($form->isValid()
            && $this->isCsrfTokenValid('save_signalement_transport', $request->request->get('_csrf_token'))
        ) {
            $files = $uploadHandlerService->handleUploadFilesRequest($request->files->get('file-upload'));
            $signalement->setPhotos($files);
            $signalementManager->save($signalement);
            $mailerProvider->sendSignalementValidationWithConseilsEviterPunaises($signalement);

            return $this->json(['response' => 'success']);
        }

        $errorMessage = [];
        /** @var FormError $error */
        foreach ($form->getErrors(true) as $error) {
            $errorMessage['signalement_transport['.$error->getOrigin()->getName().']'] = $error->getMessage();
        }

        return $this->json(['error' => $errorMessage], Response::HTTP_BAD_REQUEST);
    }
}
