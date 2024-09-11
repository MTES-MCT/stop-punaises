<?php

namespace App\Service\Signalement;

use App\Entity\Enum\SignalementStatus;
use App\Entity\Signalement;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

class StatusProvider
{
    public static function get(Security $security, Signalement $signalement): array
    {
        $signalementStatus = SignalementStatus::NEW;

        if ($signalement->getResolvedAt()
            || $signalement->getClosedAt()
            || ($signalement->isAutotraitement() && !$security->isGranted('ROLE_ADMIN'))
        ) {
            $signalementStatus = SignalementStatus::CLOSED;
        } elseif (!$signalement->isAutotraitement() && (\count($signalement->getInterventions()) > 0)) {
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
                        if (false === $intervention->isAccepted() && $intervention->getCanceledByEntrepriseAt()) {
                            $isAnnule = true;
                            break;
                        } elseif (false === $intervention->isAccepted() || false === $intervention->isAcceptedByUsager()) {
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
                $signalementStatus = SignalementStatus::PROCESSED;
            } elseif ($isAutreEntrepriseChoisie) {
                $signalementStatus = SignalementStatus::CLOSED;
            } elseif ($isAnnule) {
                $signalementStatus = SignalementStatus::CANCELED;
            } elseif ($isRefuse) {
                $signalementStatus = SignalementStatus::REFUSED;
            } elseif ($isInterventionExistante) {
                $signalementStatus = SignalementStatus::ACTIVE;
            }
        }

        return [
            'label' => $signalementStatus->label(),
            'badge' => $signalementStatus->badgeColor(),
        ];
    }
}
