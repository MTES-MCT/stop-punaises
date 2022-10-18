<?php

namespace App\Validator;

use App\Entity\Entreprise;
use App\Manager\UserManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class EmailEntrepriseUniqueValidator extends ConstraintValidator
{
    public function __construct(private UserManager $userManager)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var App\Validator\EmailEntrepriseUnique $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        /** @var Entreprise $value */
        if (null !== $value->getUser() && $value->getEmail() === $value->getUser()->getEmail()) {
            return;
        }

        $isEmailExists = $this->userManager->emailExists($value->getEmail());

        if ($isEmailExists) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value->getEmail())
                ->atPath('entreprise.email')
                ->addViolation();
        }
    }
}
