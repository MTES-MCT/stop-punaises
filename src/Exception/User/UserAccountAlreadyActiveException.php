<?php

namespace App\Exception\User;

class UserAccountAlreadyActiveException extends \Exception
{
    public function __construct(string $email = '')
    {
        parent::__construct(\sprintf('Le compte de %s est déjà actif. Si vous avez oublié votre mot de passe, cliquez ci-dessous pour en demander un nouveau.', $email));
    }
}
