<?php

namespace App\Validator;

use App\Manager\UserManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class EmailEntrepriseUniqueValidator extends ConstraintValidator
{
    public function __construct(private UserManager $userManager)
    {
    }

    public function validate($value, Constraint $constraint)
    {
        /* @var App\Validator\EmailEntrepriseUnique $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        $isEmailExists = $this->userManager->emailExists($value);

        if ($isEmailExists) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
