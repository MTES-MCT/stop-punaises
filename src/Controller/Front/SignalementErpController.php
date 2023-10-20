<?php

namespace App\Controller\Front;

use App\Entity\Signalement;
use App\Form\SignalementErpType;
use App\Manager\SignalementManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SignalementErpController extends AbstractController
{
    #[Route('/signalement/erp', name: 'app_signalement_erp')]
    public function index(): Response
    {
        $form = $this->createForm(SignalementErpType::class, new Signalement());

        return $this->render('front_signalement_erp/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/signalement/erp/ajout', name: 'app_signalement_erp_save', methods: ['POST'])]
    public function save(
        Request $request,
        SignalementManager $signalementManager,
    ): Response {
        $signalement = new Signalement();
        $form = $this->createForm(SignalementErpType::class, $signalement);
        $form->handleRequest($request);

        if ($form->isValid() &&
            $this->isCsrfTokenValid('save_signalement_erp', $request->request->get('_csrf_token'))
        ) {
            $signalementManager->save($signalement);

            return $this->json(['response' => 'success']);
        }

        $errorMessage = [];
        /** @var FormError $error */
        foreach ($form->getErrors(true) as $error) {
            $errorMessage[] = 'signalement_front['.$error->getOrigin()->getName().']';
        }

        return $this->json(['error' => $errorMessage], Response::HTTP_BAD_REQUEST);
    }
}
