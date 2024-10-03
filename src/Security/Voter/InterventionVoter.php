<?php

namespace App\Security\Voter;

use App\Entity\Intervention;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class InterventionVoter extends Voter
{
    public const SEND_ESTIMATION = 'INTERVENTION_SEND_ESTIMATION';
    public const RESOLVE = 'INTERVENTION_RESOLVE';
    public const STOP = 'INTERVENTION_STOP';

    protected function supports(string $attribute, $subject): bool
    {
        return \in_array($attribute, [self::RESOLVE, self::SEND_ESTIMATION, self::STOP]) && $subject instanceof Intervention;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!($user instanceof UserInterface)) {
            return false;
        }

        if (self::RESOLVE == $attribute) {
            return $this->canResolve($subject);
        }

        if (self::SEND_ESTIMATION == $attribute) {
            return $this->canSendEstimation($subject);
        }

        if (self::STOP == $attribute) {
            return $this->canStop($subject);
        }

        return false;
    }

    private function canResolve(Intervention $intervention): bool
    {
        $signalement = $intervention->getSignalement();

        if (!$signalement->getResolvedAt()
            && !$signalement->getClosedAt()
            && $intervention->isAccepted()
            && $intervention->getEstimationSentAt()
            && !$intervention->getCanceledByEntrepriseAt()
            && $intervention->isAcceptedByUsager()
            && !$intervention->getResolvedByEntrepriseAt()) {
            return true;
        }

        return false;
    }

    private function canSendEstimation(Intervention $intervention): bool
    {
        if ($intervention->isAccepted()) {
            return true;
        }

        return false;
    }

    private function canStop(Intervention $intervention): bool
    {
        $signalement = $intervention->getSignalement();

        if (!$signalement->getResolvedAt()
            && !$signalement->getClosedAt()
            && $intervention->isAccepted()
            && $intervention->getEstimationSentAt()
            && !$intervention->getCanceledByEntrepriseAt()
            && ($intervention->getChoiceByEntrepriseAt() || $intervention->isAcceptedByUsager())
            && !$signalement->getTypeIntervention()) {
            return true;
        }

        return false;
    }
}
