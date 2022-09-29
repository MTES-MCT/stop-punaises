<?php

namespace App\Form;

use App\Entity\Signalement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
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
            ->add('codeInsee', HiddenType::class, [
                'attr' => [
                    'class' => 'fr-hidden',
                    'pattern' => '[0-9]{5}',
                ]
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
                'label' => 'Construit avant 1948',
                'required' => false,
            ])

            
            ->add('nomOccupant', TextType::class, [
                'attr' => [
                    'class' => 'fr-input',
                    'maxlength' => '100',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Nom',
                'required' => true,
            ])
            ->add('prenomOccupant', TextType::class, [
                'attr' => [
                    'class' => 'fr-input',
                    'maxlength' => '100',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Prénom',
                'required' => true,
            ])
            ->add('telephoneOccupant', TelType::class, [
                'attr' => [
                    'class' => 'fr-input',
                    'maxlength' => '100',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Téléphone (facultatif)',
                'required' => false,
            ])
            ->add('emailOccupant', EmailType::class, [
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


            // Onglet 2
            ->add('entreprise', ChoiceType::class, [
                'attr' => [
                    'class' => 'fr-select',
                ],
                'choices' => [
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => "Entreprise",
                'required' => true,
                'placeholder' => "Entreprise",
            ])
            ->add('typeIntervention', ChoiceType::class, [
                'attr' => [
                    'class' => 'fr-select',
                ],
                'choices' => [
                    'Diagnostic' => 'diagnostic',
                    'Traitement' => 'traitement',
                    'Les deux' => 'diagnostic-traitement',
                ],
                'row_attr' => [
                    'class' => 'fr-select-group',
                ],
                'required' => true,
                'placeholder' => "Type d'intervention",
            ])
            ->add('dateIntervention', DateType::class, [
                'attr' => [
                    'class' => 'fr-input',
                ],
                'row_attr' => [
                    'class' => 'fr-input-group',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => "Date de l'intervention",
                'required' => true,
            ])
            ->add('nomAgentIntervention', ChoiceType::class, [
                'attr' => [
                    'class' => 'fr-select',
                ],
                'choices' => [
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => "Nom de l'agent",
                'required' => true,
                'placeholder' => "Nom de l'agent",
            ])
            ->add('niveauInfestation', ChoiceType::class, [
                'attr' => [
                    'class' => 'fr-select',
                ],
                'choices' => [0, 1, 2, 3, 4],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => "Niveau d'infestation",
                'required' => true,
                'placeholder' => "Niveau d'infestation",
            ])
            ->add('typeTraitement', ChoiceType::class, [
                'attr' => [
                    'class' => 'fr-select',
                ],
                'choices' => [
                    'Vapeur' => 'vapeur',
                    'Froid' => 'froid',
                    'Tente chauffante' => 'tente-chauffante',
                    'Aspiration' => 'aspiration',
                    'Biocide' => 'biocide',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => "Type de traitement",
                'row_attr' => [
                    'class' => 'fr-select-group',
                ],
                'required' => false,
                'placeholder' => 'Type de traitement',
            ])
            ->add('nomBiocide', TelType::class, [
                'attr' => [
                    'class' => 'fr-input',
                    'maxlength' => '100',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Nom du biocide',
                'required' => false,
            ])
            ->add('typeDiagnostic', ChoiceType::class, [
                'choice_attr' => [
                    'class' => 'fr-radio',
                ],
                'choices' => [
                    'Visuel' => 'visuel',
                    'Canin' => 'canin',
                ],
                'expanded' => true,
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Type de diagnostic',
                'required' => false,
            ])

            ->add('nombrePiecesTraitees', IntegerType::class, [
                'attr' => [
                    'class' => 'fr-input',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Nombre de pièces traitées',
                'required' => true,
            ])
            ->add('delaiEntreInterventions', IntegerType::class, [
                'attr' => [
                    'class' => 'fr-input',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Délai entre les interventions (en jours)',
                'required' => true,
            ])
            ->add('faitVisitePostTraitement', ChoiceType::class, [
                'choice_attr' => [
                    'class' => 'fr-radio',
                ],
                'choices' => [
                    'Oui' => 1,
                    'Non' => 0,
                ],
                'expanded' => true,
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Visite post-traitement',
                'required' => false,
            ])
            ->add('dateVisitePostTraitement', DateType::class, [
                'format' => 'dd-MM-yyyy',
                'attr' => [
                    'class' => 'fr-input',
                ],
                'row_attr' => [
                    'class' => 'fr-input-group',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => "Date de la visite post-traitement",
                'required' => true,
            ])
            ->add('prixFactureHT', TextType::class, [
                'attr' => [
                    'class' => 'fr-input',
                    'maxlength' => '100',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Prix facturé (en €)',
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Signalement::class,
        ]);
    }
}
