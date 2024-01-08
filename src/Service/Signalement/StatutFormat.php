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

    private string $badgeName;
    private string $label;

    public function __construct(
        private Security $security,
        private Signalement $signalement
    ) {
        $this->init();
    }

    public static function getBadgeNameByLabel(string $label): string
    {
        return self::BADGE_BY_LABEL[$label];
    }

    public function getBadgeName(): string
    {
        return $this->badgeName;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    private function init(): void
    {
        $this->label = 'Nouveau';

        if ($this->signalement->getResolvedAt()
            || $this->signalement->getClosedAt()
            || ($this->signalement->isAutotraitement() && !$this->security->isGranted('ROLE_ADMIN'))
        ) {
            $this->label = 'Fermé';
        } elseif (!$this->signalement->isAutotraitement() && !empty($this->signalement->getInterventions())) {
            $isAnnule = false;
            $isRefuse = false;
            $isInterventionExistante = false;
            $isTraite = false;
            $isAutreEntrepriseChoisie = false;

            if (!$this->security->isGranted('ROLE_ADMIN')) {
                /** @var User $user */
                $user = $this->security->getUser();
                $entreprise = $user->getEntreprise();
                foreach ($this->signalement->getInterventions() as $intervention) {
                    if ($intervention->getEntreprise() == $entreprise) {
                        $isInterventionExistante = true;
                        if (!$intervention->isAccepted() && $intervention->getCanceledByEntrepriseAt()) {
                            $isAnnule = true;
                            break;
                        } elseif (!$intervention->isAccepted() || !$intervention->isAcceptedByUsager()) {
                            $isRefuse = true;
                            break;
                        } elseif ($intervention->isAccepted() && $intervention->isAcceptedByUsager() && !empty($this->signalement->getTypeIntervention())) {
                            $isTraite = true;
                            break;
                        }
                    } elseif ($intervention->isAccepted() && $intervention->isAcceptedByUsager()) {
                        $isAutreEntrepriseChoisie = true;
                        break;
                    }
                }
            } elseif (!empty($this->signalement->getTypeIntervention())) {
                $isTraite = true;
            } else {
                $isInterventionExistante = true;
            }

            if ($isTraite) {
                $this->label = 'Traité';
            } elseif ($isAutreEntrepriseChoisie) {
                $this->label = 'Fermé';
            } elseif ($isAnnule) {
                $this->label = 'Annulé';
            } elseif ($isRefuse) {
                $this->label = 'Refusé';
            } elseif ($isInterventionExistante) {
                $this->label = 'En cours';
            }
        }

        $this->badgeName = self::BADGE_BY_LABEL[$this->label];
    }
}
