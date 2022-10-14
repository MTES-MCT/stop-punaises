<?php

namespace App\Service\Token;

use App\Entity\Enum\Status;
use App\Entity\User;
use App\Repository\UserRepository;

class ResetPasswordToken extends AbstractGeneratorToken
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function validateToken(string $token): bool|User
    {
        $user = $this->userRepository->findOneBy(['token' => $token]);

        if ($this->canResetPassword($user)) {
            return $user;
        }

        return false;
    }

    public function canResetPassword(?User $user): bool
    {
        return null !== $user
            && new \DateTimeImmutable() < $user->getTokenExpiredAt()
            && Status::ACTIVE === $user->getStatus();
    }
}
