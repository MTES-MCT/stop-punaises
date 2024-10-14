<?php

namespace App\Security\Voter;

use App\Entity\Enum\Role;
use App\Entity\Signalement;
use App\Repository\InterventionRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class SignalementVoter extends Voter
{
    public const ACCEPT = 'SIGNALEMENT_ACCEPT';
    public const CLOSE = 'SIGNALEMENT_CLOSE';

    public function __construct(
        private Security $security,
        private InterventionRepository $interventionRepository,
    ) {
    }

    protected function supports(string $attribute, $subject): bool
    {
        return \in_array($attribute, [self::ACCEPT, self::CLOSE]) && $subject instanceof Signalement;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!($user instanceof UserInterface)) {
            return false;
        }

        if (self::ACCEPT == $attribute) {
            return $this->canAccept($subject);
        }

        if (self::CLOSE == $attribute) {
            return $this->canClose($subject);
        }

        return false;
    }

    private function canAccept(Signalement $signalement): bool
    {
        if ($this->security->isGranted(Role::ROLE_ADMIN->value)) {
            return false;
        }

        $acceptedEstimations = $this->interventionRepository->findBy([
            'signalement' => $signalement,
            'acceptedByUsager' => true,
            'canceledByEntrepriseAt' => null,
        ]);
        if (!$signalement->getResolvedAt()
            && !$signalement->getClosedAt()
            && 0 == \count($acceptedEstimations)) {
            return true;
        }

        return false;
    }

    private function canClose(Signalement $signalement): bool
    {
        if ($this->security->isGranted(Role::ROLE_ADMIN->value)
            && !$signalement->getResolvedAt()
            && !$signalement->getClosedAt()) {
            return true;
        }

        return false;
    }
}
