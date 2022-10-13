<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;

class ResetPasswordToken
{
    public const LENGTH = 32;

    public function __construct(private UserRepository $userRepository)
    {
    }

    public function generateToken()
    {
        return bin2hex(openssl_random_pseudo_bytes(self::LENGTH));
    }

    public function validateToken(string $token): bool|User
    {
        $user = $this->userRepository->findOneBy(['confirmationToken' => $token]);

        if (null === $user || new \DateTimeImmutable() > $user->getPasswordRequestExpiredAt()) {
            return false;
        }

        return $user;
    }
}
