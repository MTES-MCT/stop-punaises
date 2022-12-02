<?php

namespace App\Controller\Front;

use App\Entity\Enum\InfestationLevel;
use App\Entity\Signalement;
use App\Manager\SignalementManager;
use App\Service\Signalement\EventsProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SuiviUsagerViewController extends AbstractController
{
    #[Route('/signalements/{uuid}', name: 'app_suivi_usager_view')]
    public function suivi_usager(Signalement $signalement): Response
    {
        $eventsProvider = new EventsProvider($signalement, $this->getParameter('doc_autotraitement'));
        $events = $eventsProvider->getEvents();

        return $this->render('front_suivi_usager/index.html.twig', [
            'signalement' => $signalement,
            'link_pdf_autotraitement' => $this->getParameter('doc_autotraitement'),
            'niveau_infestation' => InfestationLevel::from($signalement->getNiveauInfestation())->label(),
            'events' => $events,
        ]);
    }

    #[Route('/signalements/{uuid}/resoudre', name: 'app_signalement_resolve')]
    public function signalement_resolu(
        Request $request,
        Signalement $signalement,
        SignalementManager $signalementManager
        ): Response {
        if ($this->isCsrfTokenValid('signalement_resolve', $request->get('_csrf_token'))) {
            $this->addFlash('success', 'Votre procédure est terminée !');
            $signalement->setResolvedAt(new \DateTimeImmutable());
            $signalementManager->save($signalement);
        }

        return $this->redirectToRoute('app_suivi_usager_view', ['uuid' => $signalement->getUuid()]);
    }
}
