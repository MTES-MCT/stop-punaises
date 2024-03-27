<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserTest extends KernelTestCase
{
    /**
     * @dataProvider provideInvalidPassword
     */
    public function testPasswordValidationError(string $expectedResult, string $password)
    {
        /** @var ValidatorInterface $validator */
        $validator = static::getContainer()->get(ValidatorInterface::class);

        $user = new User();
        $user->setPassword($password);

        $errors = $validator->validate($user, null, ['password']);

        /** @var ConstraintViolationList $errors */
        $errorsAsString = (string) $errors;
        $this->assertStringContainsString($expectedResult, $errorsAsString);
    }

    public function testPasswordValidationSuccess()
    {
        /** @var ValidatorInterface $validator */
        $validator = static::getContainer()->get(ValidatorInterface::class);

        $user = new User();
        $user->setPassword('Stop-Punaise01');

        $errors = $validator->validate($user, null, ['password']);

        $this->assertCount(0, $errors);
    }

    public function provideInvalidPassword(): \Generator
    {
        yield 'blank' => ['Cette valeur ne doit pas être vide', ''];
        yield 'short' => ['Le mot de passe doit contenir au moins 8 caractères', 'short'];
        yield 'no_uppercase' => ['Le mot de passe doit contenir au moins une lettre majuscule', 'nouppercase'];
        yield 'no_lowercase' => ['Le mot de passe doit contenir au moins une lettre minuscule', 'NOLOWERCASE'];
        yield 'no_digit' => ['Le mot de passe doit contenir au moins un chiffre', 'NoDigitNoDigit'];
        yield 'no_special' => ['Le mot de passe doit contenir au moins un caractère spécial', 'NoSpecial'];
    }
}
