<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Email;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'row_attr' => [
                    'class' => 'fr-input-group fr-col-12',
                ],
                'attr' => [
                    'class' => 'fr-input',
                    'placeholder' => 'Claude Petit',
                    'autocomplete' => 'name',
                ],
                'label' => 'Votre nom',
                'constraints' => [
                    new Assert\NotBlank(message: 'Merci de renseigner votre nom complet.'),
                ],
            ])
            ->add('email', EmailType::class, [
                'row_attr' => [
                    'class' => 'fr-input-group fr-col-12',
                ],
                'attr' => [
                    'class' => 'fr-input',
                    'placeholder' => 'claude.petit@courriel.fr',
                    'autocomplete' => 'email',
                ],
                'label' => 'Votre adresse courriel <span class="fr-hint-text">Format attendu : nom@domaine.fr</span>',
                'label_html' => true,
                'constraints' => [
                    new Assert\NotBlank(message: 'Merci de renseigner votre email.'),
                    new Email(
                        mode: Email::VALIDATION_MODE_STRICT,
                        message: 'Votre adresse e-mail doit être au bon format.'
                    ),
                ],
            ])
            ->add('message', TextareaType::class, [
                'row_attr' => [
                    'class' => 'fr-input-group fr-col-12',
                ],
                'attr' => [
                    'class' => 'fr-input',
                    'rows' => 10,
                    'minlength' => 10,
                ],
                'label' => 'Votre message <span class="fr-hint-text">Format attendu : doit comporter au moins 10 caractères.</span>',
                'label_html' => true,
                'constraints' => [
                    new Assert\NotBlank(message: 'Merci de renseigner votre message.'),
                    new Assert\Length(min: 10, minMessage: 'Votre message doit comporter au moins 10 caractères.'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => [
                'id' => 'front_contact',
                'class' => 'needs-validation',
                'novalidate' => 'novalidate',
            ],
        ]);
    }
}
