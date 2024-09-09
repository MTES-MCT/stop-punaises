<?php

namespace App\Exception\User;

class RequestPasswordNotAllowedException extends \Exception
{
    public function __construct(string $email = '')
    {
        parent::__construct(\sprintf('%s a un compte inactif. Merci d\'activer votre compte.', $email));
    }
}
