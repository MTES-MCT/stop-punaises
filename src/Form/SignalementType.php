<?php

namespace App\Form;

use App\Entity\Signalement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SignalementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('adresse', TextType::class, [
                'attr' => [
                    'class' => 'fr-input',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                    'maxlength' => '100',
                ],
                'label' => "Adresse",
            ])
            ->add('codePostal', TextType::class, [
                'attr' => [
                    'class' => 'fr-input',
                    'pattern' => '[0-9]{5}',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                    'maxlength' => '5',
                    'minlength' => '5',
                ],
                'label' => 'Code postal',
            ])
            ->add('ville', TextType::class, [
                'attr' => [
                    'class' => 'fr-input',
                    'maxlength' => '100',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Ville',
            ])
            

            ->add('typeLogement', ChoiceType::class, [
                'attr' => [
                    'class' => 'fr-select',
                ],
                'choices' => [
                    'Appartement' => 'appartement',
                    'Maison' => 'maison',
                    'Autre' => 'autre',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => "Type de logement",
                'row_attr' => [
                    'class' => 'fr-select-group',
                ],
                'required' => true,
                'placeholder' => 'Type de logement',
            ])
            ->add('localisationDansImmeuble', ChoiceType::class, [
                'attr' => [
                    'class' => 'fr-select',
                ],
                'choices' => [
                    'Logement' => 'logement',
                    'Parties communes' => 'communes',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => "Localisation de l'infestation",
                'required' => false,
                'placeholder' => "Localisation de l'infestation",
            ])
            ->add('construitAvant1948', ChoiceType::class, [
                'choice_attr' => [
                    'class' => 'fr-radio',
                ],
                'choices' => [
                    'Oui' => 1,
                    'Non' => 0,
                    'Ne sais pas' => '',
                ],
                'expanded' => true,
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'required' => false,
                'label' => 'Construit avant 1948'
            ])

            
            ->add('nomOccupant', TextType::class, [
                'attr' => [
                    'class' => 'fr-input',
                    'maxlength' => '100',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'required' => true,
                'label' => 'Nom',
            ])
            ->add('prenomOccupant', TextType::class, [
                'attr' => [
                    'class' => 'fr-input',
                    'maxlength' => '100',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'required' => true,
                'label' => 'Prénom',
            ])
            ->add('telephoneOccupant', TelType::class, [
                'attr' => [
                    'class' => 'fr-input',
                    'maxlength' => '100',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'required' => false,
                'label' => 'Téléphone (facultatif)',
            ])
            ->add('emailOccupant', EmailType::class, [
                'attr' => [
                    'class' => 'fr-input',
                    'maxlength' => '100',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'required' => false,
                'label' => 'Email (facultatif)',
            ])

            /*->add('typeIntervention')
            ->add('dateIntervention')
            ->add('nomAgentIntervention')
            ->add('niveauInfestation')
            ->add('typeTraitement')
            ->add('nomBiocide')
            ->add('typeDiagnostic')
            ->add('nombrePiecesTraitees')
            ->add('delaiEntreInterventions')
            ->add('faitVisitePostTraitement')
            ->add('dateVisitePostTraitement')
            ->add('prixFactureHT')
            ->add('entreprise')*/
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Signalement::class,
        ]);
    }
}
