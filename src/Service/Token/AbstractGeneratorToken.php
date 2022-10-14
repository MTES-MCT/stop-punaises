<?php

namespace App\Service\Token;

abstract class AbstractGeneratorToken
{
    public const LENGTH = 32;

    public function generateToken()
    {
        return bin2hex(openssl_random_pseudo_bytes(self::LENGTH));
    }
}
