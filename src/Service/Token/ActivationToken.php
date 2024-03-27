<?php

namespace App\Service\Token;

use App\Entity\Enum\Status;
use App\Entity\User;

class ActivationToken extends AbstractGeneratorToken
{
    public function validateToken(User $user, string $token): bool|User
    {
        if ($user->getToken() != $token) {
            return false;
        }

        if ($this->canActivateAccount($user) || $this->canUpdatePassword($user)) {
            return $user;
        }

        return false;
    }

    private function canActivateAccount(?User $user): bool
    {
        return null !== $user
            && new \DateTimeImmutable() < $user->getTokenExpiredAt()
            && Status::INACTIVE === $user->getStatus();
    }

    private function canUpdatePassword(?User $user): bool
    {
        return null !== $user
            && new \DateTimeImmutable() < $user->getTokenExpiredAt()
            && Status::ACTIVE === $user->getStatus();
    }
}
