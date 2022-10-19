<?php

namespace App\Form;

use App\Entity\Entreprise;
use App\Entity\Territoire;
use App\Repository\TerritoireRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntrepriseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Entreprise $entreprise */
        $entreprise = $builder->getData();
        $email = $entreprise->getUser()?->getEmail();
        $builder
            ->add('nom', TextType::class, [
                'attr' => [
                    'class' => 'fr-input',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                    'maxlength' => '255',
                ],
                'label' => 'Nom / raison sociale',
                'required' => true,
            ])
            ->add('numeroSiret', TextType::class, [
                'attr' => [
                    'class' => 'fr-input',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                    'maxlength' => '50',
                ],
                'label' => 'SIRET',
                'required' => true,
            ])
            ->add('telephone', TelType::class, [
                'attr' => [
                    'class' => 'fr-input',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                    'maxlength' => '20',
                ],
                'label' => 'Téléphone',
                'required' => true,
            ])
            ->add('numeroLabel', TextType::class, [
                'attr' => [
                    'class' => 'fr-input',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                    'maxlength' => '100',
                ],
                'label' => 'Numéro de label',
                'required' => true,
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'fr-input',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                    'maxlength' => '100',
                ],
                'label' => 'Email',
                'required' => true,
                'data' => $email,
            ])
            ->add('territoires', EntityType::class, [
                'class' => Territoire::class,
                'query_builder' => function (TerritoireRepository $er) {
                    return $er->createQueryBuilder('e')->orderBy('e.id', 'ASC');
                },
                'attr' => [
                    'class' => 'fr-select',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Territoires d\'intervention',
                'placeholder' => 'Département',
                'help' => 'Maintenez la touche CTRL enfoncée pour sélectionner plusieurs territoires',
                'help_attr' => [
                    'class' => 'fr-hint-text',
                ],
                'required' => true,
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Entreprise::class,
        ]);
    }
}
