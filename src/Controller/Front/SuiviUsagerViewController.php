<?php

namespace App\Controller\Front;

use App\Entity\Enum\InfestationLevel;
use App\Entity\Signalement;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SuiviUsagerViewController extends AbstractController
{
    #[Route('/signalements/{uuid}', name: 'app_suivi_usager_view')]
    public function suivi_usager(Request $request, Signalement $signalement): Response
    {
        $events = $this->getEvents($signalement);

        return $this->render('front_suivi_usager/index.html.twig', [
            'signalement' => $signalement,
            'steps' => $this->getStepInfo($signalement),
            'link_pdf_autotraitement' => $this->getParameter('doc_autotraitement'),
            'niveau_infestation' => InfestationLevel::from($signalement->getNiveauInfestation())->label(),
            'events' => $events,
        ]);
    }

    private function getStepInfo($signalement)
    {
        $stepMax = $signalement->isAutotraitement() ? 3 : 4;
        $stepIndex = 2;
        $stepLabel = '';
        if ($signalement->isAutotraitement()) {
            $stepLabel = $stepIndex > 2 ? 'Signalement cloturé' : 'Traitement terminé';
        } else {
            switch ($stepIndex) {
                case 1:
                    $stepLabel = 'Signalement déposé';
                    break;
                case 2:
                    $stepLabel = 'Echanges entreprises';
                    break;
                case 3:
                    $stepLabel = 'Traitement en cours';
                    break;
                default:
                    $stepLabel = 'Traitement terminé';
                    break;
            }
        }

        return [
            'stepIndex' => $stepIndex,
            'stepMax' => $stepMax,
            'stepLabel' => $stepLabel,
        ];
    }

    private function getEvents(Signalement $signalement): array
    {
        $buffer = [];

        if ($signalement->isAutotraitement()) {
            $todayDate = new DateTime();
            if ($todayDate->diff($signalement->getCreatedAt())->format('%a') >= 45) {
                $buffer[] = [
                    'date' => $todayDate,
                    'label' => 'Nouveau',
                    'title' => 'Suivi du traitement',
                    'description' => 'Votre problème de punaises est-il résolu ?',
                    'actionLabel' => 'En savoir plus',
                    'actionLink' => '#',
                ];
            }

            $buffer[] = [
                'date' => $signalement->getCreatedAt(),
                'title' => 'Protocole envoyé',
                'description' => 'Le protocole d\'auto traitement a bien été envoyé.',
                'actionLabel' => 'Télécharger le protocole',
                'actionLink' => $this->getParameter('doc_autotraitement'),
            ];
        } else {
            $todayDate = new DateTime();
            // TODO : comparer avec closedDate + 15j
            if (false && $todayDate->diff($signalement->getCreatedAt())->format('%a') >= 15) {
                $buffer[] = [
                    'date' => $todayDate,
                    'label' => 'Nouveau',
                    'title' => 'Suivi du traitement',
                    'description' => 'Votre problème de punaises est-il résolu ?',
                    'actionLabel' => 'En savoir plus',
                    'actionLink' => '#',
                ];
            }
        }

        $buffer[] = [
            'date' => $signalement->getCreatedAt(),
            'title' => 'Signalement déposé',
            'description' => 'Votre signalement a bien été enregistré sur Stop Punaises.',
        ];

        return $buffer;
    }
}
