<?php

namespace App\Service\Signalement;

use App\Entity\Signalement;
use Symfony\Bundle\SecurityBundle\Security;

class StatutFormat
{
    private const BADGE_BY_LABEL = [
        'Nouveau' => 'orange-terre-battue',
        'Fermé' => 'blue-ecume',
        'Traité' => 'green-menthe',
        'Annulé' => 'beige-gris-galet',
        'Refusé' => 'beige-gris-galet',
        'En cours' => 'success',
    ];

    public static function getBadgeNameByLabel(string $label): string
    {
        return self::BADGE_BY_LABEL[$label];
    }

    public static function getFormat(Security $security, Signalement $signalement): array
    {
        $label = 'Nouveau';

        if ($signalement->getResolvedAt()
            || $signalement->getClosedAt()
            || ($signalement->isAutotraitement() && !$security->isGranted('ROLE_ADMIN'))
        ) {
            $label = 'Fermé';
        } elseif (!$signalement->isAutotraitement() && !empty($signalement->getInterventions())) {
            $isAnnule = false;
            $isRefuse = false;
            $isInterventionExistante = false;
            $isTraite = false;
            $isAutreEntrepriseChoisie = false;

            if (!$security->isGranted('ROLE_ADMIN')) {
                /** @var User $user */
                $user = $security->getUser();
                $entreprise = $user->getEntreprise();
                foreach ($signalement->getInterventions() as $intervention) {
                    if ($intervention->getEntreprise() == $entreprise) {
                        $isInterventionExistante = true;
                        if (!$intervention->isAccepted() && $intervention->getCanceledByEntrepriseAt()) {
                            $isAnnule = true;
                            break;
                        } elseif (!$intervention->isAccepted() || !$intervention->isAcceptedByUsager()) {
                            $isRefuse = true;
                            break;
                        } elseif ($intervention->isAccepted() && $intervention->isAcceptedByUsager() && !empty($signalement->getTypeIntervention())) {
                            $isTraite = true;
                            break;
                        }
                    } elseif ($intervention->isAccepted() && $intervention->isAcceptedByUsager()) {
                        $isAutreEntrepriseChoisie = true;
                        break;
                    }
                }
            } elseif (!empty($signalement->getTypeIntervention())) {
                $isTraite = true;
            } else {
                $isInterventionExistante = true;
            }

            if ($isTraite) {
                $label = 'Traité';
            } elseif ($isAutreEntrepriseChoisie) {
                $label = 'Fermé';
            } elseif ($isAnnule) {
                $label = 'Annulé';
            } elseif ($isRefuse) {
                $label = 'Refusé';
            } elseif ($isInterventionExistante) {
                $label = 'En cours';
            }
        }

        return [
            'label' => $label,
            'badge' => self::BADGE_BY_LABEL[$label],
        ];
    }
}
