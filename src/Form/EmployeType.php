<?php

namespace App\Form;

use App\Entity\Employe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmployeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'attr' => [
                    'class' => 'fr-input',
                    'maxlength' => '50',
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
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Téléphone (facultatif)',
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'fr-input',
                    'maxlength' => '100',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Email (facultatif)',
                'required' => false,
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
