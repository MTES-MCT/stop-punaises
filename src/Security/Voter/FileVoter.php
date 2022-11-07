<?php

namespace App\Security\Voter;

use App\Entity\Signalement;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class FileVoter extends Voter
{
    public const DELETE = 'FILE_DELETE';
    public const VIEW = 'FILE_VIEW';
    public const CREATE = 'FILE_CREATE';

    protected function supports(string $attribute, $subject): bool
    {
        return \in_array($attribute, [self::DELETE, self::VIEW, self::CREATE])
            && ($subject instanceof Signalement || 'boolean' === \gettype($subject));
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if ($user instanceof UserInterface) {
            return \in_array('ROLE_ADMIN', $user->getRoles());
        }

        return false;
    }
}
