<?php

namespace App\Security\Voter;

use App\Entity\Signalement;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class FileVoter extends Voter
{
    public const DELETE = 'FILE_DELETE';

    protected function supports(string $attribute, $subject): bool
    {
        return \in_array($attribute, [self::DELETE])
            && ($subject instanceof Signalement || 'boolean' === \gettype($subject));
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if ($user instanceof UserInterface && self::DELETE == $attribute) {
            return $this->canDelete($subject, $user);
        }

        return false;
    }

    private function canDelete(Signalement $signalement, UserInterface $user): bool
    {
        /** @var User $user */
        if (\in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        } elseif (null !== $user->getEntreprise() && null !== $signalement->getEntreprise()) {
            return $user->getEntreprise()->getId() === $signalement->getEntreprise()?->getId();
        }

        return false;
    }
}
