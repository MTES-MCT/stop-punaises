<?php

namespace App\Controller\Security\Utils;

use Symfony\Component\HttpFoundation\Request;

trait ValidatorPasswordResetableTrait
{
    /** todo: replace by symfony validator */
    public function validate(Request $request): array
    {
        $errors = [];
        if ($request->get('password') !== $request->get('password-repeat')) {
            $errors['password'][] = 'Les deux mots de passe ne correspondent pas';
        }

        if (empty($request->get('password'))) {
            $errors['password'][] = 'Le mot de passe ne pas être vide';
        }

        return $errors;
    }
}
