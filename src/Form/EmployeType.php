<?php

namespace App\Form;

use App\Entity\Employe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Email;

class EmployeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'attr' => [
                    'class' => 'fr-input',
                    'maxlength' => '50',
                    'placeholder' => 'Petit',
                    'autocomplete' => 'family-name',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Nom',
                'required' => true,
            ])
            ->add('prenom', TextType::class, [
                'attr' => [
                    'class' => 'fr-input',
                    'maxlength' => '50',
                    'placeholder' => 'Claude',
                    'autocomplete' => 'given-name',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Prénom',
                'required' => true,
            ])
            ->add('numeroCertification', TextType::class, [
                'attr' => [
                    'class' => 'fr-input',
                    'maxlength' => '50',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Certification Biocide',
                'required' => true,
            ])
            ->add('telephone', TelType::class, [
                'attr' => [
                    'class' => 'fr-input',
                    'pattern' => '^(?:0|\(?\+33\)?\s?|0033\s?)[1-9](?:[\.\-\s]?\d\d){4}$',
                    'maxlength' => '20',
                    'placeholder' => '0706050403',
                    'autocomplete' => 'tel-national',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Téléphone (facultatif)',
                'help' => 'Format attendu : 10 chiffres',
                'help_attr' => [
                    'class' => 'fr-hint-text',
                ],
                'required' => false,
                'constraints' => [
                    new Assert\Regex('/[0-9]{10}/', 'Veuillez renseigner un numéro de téléphone valide'),
                ],
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'fr-input',
                    'maxlength' => '100',
                    'placeholder' => 'claude.petit@courriel.fr',
                    'autocomplete' => 'email',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Email (facultatif)',
                'help' => 'Format attendu : nom@domaine.fr',
                'help_attr' => [
                    'class' => 'fr-hint-text',
                ],
                'required' => false,
                'constraints' => [
                    new Email(
                        mode: Email::VALIDATION_MODE_STRICT,
                        message: 'Veuillez renseigner un e-mail valide.'
                    ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Employe::class,
        ]);
    }
}
