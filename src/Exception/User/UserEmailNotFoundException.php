<?php

namespace App\Exception\User;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserEmailNotFoundException extends NotFoundHttpException
{
    public function __construct(string $email = '')
    {
        parent::__construct(\sprintf('%s ne correspond à aucun compte, verifiez votre saisie', $email));
    }
}
