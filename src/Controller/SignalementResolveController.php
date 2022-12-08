<?php

namespace App\Controller;

use App\Entity\Signalement;
use App\Form\SignalementType;
use App\Manager\SignalementManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SignalementResolveController extends AbstractController
{
    #[Route('/bo/signalements/{uuid}/traiter', name: 'app_signalement_resolve')]
    public function index(
        Request $request,
        Signalement $signalement,
        SignalementManager $signalementManager,
        ): Response {
        $form = $this->createForm(SignalementType::class, $signalement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $signalementManager->save($signalement);

            $this->addFlash('success', 'Le signalement a bien été enregistré.');

            return $this->redirect($this->generateUrl('app_signalement_view', ['uuid' => $signalement->getUuid()]));
        }

        $this->displayErrors($form);

        return $this->render('signalement_create/index.html.twig', [
            'form' => $form->createView(),
            'app_name' => 'app_signalement_resolve',
            'uuid' => $signalement->getUuid(),
        ]);
    }

    private function displayErrors(FormInterface $form): void
    {
        /** @var FormError $error */
        foreach ($form->getErrors(true) as $error) {
            $this->addFlash('error', $error->getMessage());
        }
    }
}
