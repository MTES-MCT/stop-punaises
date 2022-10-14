<?php

namespace App\Exception\User;

class UserAccountAlreadyActiveException extends \Exception
{
    public function __construct(string $email = '')
    {
        parent::__construct(sprintf('%s a déja un compte actif', $email));
    }
}
