<?php

namespace App\Service;

use Symfony\Component\Form\FormInterface;

class FormHelper
{
    public static function getErrorsFromForm(FormInterface $form, $recursive = false): array
    {
        $errors = [];
        foreach ($form->getErrors() as $error) {
            if ($recursive) {
                $errors[] = $error->getMessage();
            } else {
                $errors['__nopath__']['errors'][] = $error->getMessage();
            }
        }
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = self::getErrorsFromForm($childForm, true)) {
                    foreach ($childErrors as $childError) {
                        $errors[$childForm->getName()]['errors'][] = $childError;
                    }
                }
            }
        }

        return $errors;
    }
}
