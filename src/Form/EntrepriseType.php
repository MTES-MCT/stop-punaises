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
                    'maxlength' => '255',
                    'placeholder' => 'SARL Stop Punaises',
                    'autocomplete' => 'organization',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Nom / raison sociale',
                'required' => true,
            ])
            ->add('numeroSiret', TextType::class, [
                'attr' => [
                    'class' => 'fr-input',
                    'maxlength' => '50',
                    'placeholder' => '12345678901234',
                    'autocomplete' => 'off',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'SIRET',
                'required' => true,
            ])
            ->add('telephone', TelType::class, [
                'attr' => [
                    'class' => 'fr-input',
                    'pattern' => '^(?:0|\(?\+33\)?\s?|0033\s?)[1-9](?:[\.\-\s]?\d\d){4}$',
                    'maxlength' => '20',
                    'placeholder' => '0605040302',
                    'autocomplete' => 'tel-national',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Téléphone',
                'required' => true,
            ])
            ->add('numeroLabel', TextType::class, [
                'attr' => [
                    'class' => 'fr-input',
                    'maxlength' => '100',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Numéro de label',
                'required' => true,
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'fr-input',
                    'maxlength' => '100',
                    'placeholder' => 'contact@sarl-stop-punaises.fr',
                    'autocomplete' => 'email',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
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
