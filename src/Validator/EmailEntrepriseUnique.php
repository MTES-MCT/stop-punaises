<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class EmailEntrepriseUnique extends Constraint
{
    /*
     * Any public properties become valid options for the annotation.
     * Then, use these in your validator class.
     */
    public string $message = 'L\'email "{{ value }}" est déja utilisée, merci de saisir un nouvel email.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
