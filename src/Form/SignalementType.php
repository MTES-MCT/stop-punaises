<?php

namespace App\Form;

use App\Entity\Employe;
use App\Entity\Entreprise;
use App\Entity\Enum\InfestationLevel;
use App\Entity\Signalement;
use App\Repository\EmployeRepository;
use App\Repository\EntrepriseRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class SignalementType extends AbstractType
{
    public function __construct(private Security $security)
    {
    }

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
                'label' => 'Adresse',
                'required' => true,
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
                'required' => true,
            ])
            ->add('codeInsee', HiddenType::class, [
                'attr' => [
                    'class' => 'fr-hidden',
                    'pattern' => '[0-9]{5}',
                ],
                'required' => false,
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
                'required' => true,
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
                'label' => 'Type de logement',
                'row_attr' => [
                    'class' => 'fr-select-group',
                ],
                'placeholder' => 'Type de logement',
                'required' => true,
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
                'placeholder' => "Localisation de l'infestation",
                'required' => false,
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
                'placeholder' => "Type d'intervention",
                'required' => true,
            ])
            ->add('dateIntervention', DateType::class, [
                'widget' => 'single_text',
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
            ->add('niveauInfestation', EnumType::class, [
                'attr' => [
                    'class' => 'fr-select',
                ],
                'class' => InfestationLevel::class,
                'choice_label' => function (InfestationLevel $infestationLevel) {
                    return $infestationLevel ? $infestationLevel->label() : '';
                },

                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => "Niveau d'infestation",
                'placeholder' => "Niveau d'infestation",
                'required' => true,
            ])
            ->add('typeTraitement', ChoiceType::class, [
                'attr' => [
                    'class' => 'fr-select',
                    'size' => 5,
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
                'multiple' => true,
                'help' => 'Maintenez la touche CTRL enfoncée pour sélectionner plusieurs types de traitement',
                'help_attr' => [
                    'class' => 'fr-hint-text',
                ],
                'label' => 'Type de traitement',
                'row_attr' => [
                    'class' => 'fr-select-group',
                ],
                'placeholder' => 'Type de traitement',
                'required' => false,
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
                'placeholder' => false,
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
                'required' => false,
            ])
            ->add('delaiEntreInterventions', IntegerType::class, [
                'attr' => [
                    'class' => 'fr-input',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Délai entre les interventions (en jours)',
                'required' => false,
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
                'placeholder' => false,
                'required' => false,
            ])
            ->add('dateVisitePostTraitement', DateType::class, [
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'fr-input',
                ],
                'row_attr' => [
                    'class' => 'fr-input-group',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Date de la visite post-traitement',
                'required' => false,
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

        $builder->get('niveauInfestation')
            ->addModelTransformer(new CallbackTransformer(
                function (?int $level) {
                    return InfestationLevel::from($level);
                },
                function (InfestationLevel $level) {
                    return $level->value;
                }
            ));

        if ($this->security->isGranted('ROLE_ADMIN')) {
            $this->buildFieldsAdmin($builder);
        } else {
            $this->buildFieldsEntreprise($builder);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Signalement::class,
        ]);
    }

    private function buildFieldsAdmin(FormBuilderInterface $builder): void
    {
        $builder
            ->add('entreprise', EntityType::class, [
                'class' => Entreprise::class,
                'query_builder' => function (EntrepriseRepository $er) {
                    return $er->createQueryBuilder('e')->orderBy('e.id', 'ASC');
                },
                'attr' => [
                    'class' => 'fr-select',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Entreprise',
                'placeholder' => 'Entreprise',
                'required' => true,
            ])
            ->add('agent', EntityType::class, [
                'class' => Employe::class,
                'query_builder' => function (EmployeRepository $er) {
                    return $er->createQueryBuilder('e')->orderBy('e.id', 'ASC');
                },
                'attr' => [
                    'class' => 'fr-select',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => "Nom de l'agent",
                'placeholder' => "Nom de l'agent",
                'required' => true,
            ]);
    }

    private function buildFieldsEntreprise(FormBuilderInterface $builder): void
    {
        $builder
            ->add('agent', EntityType::class, [
                'class' => Employe::class,
                'query_builder' => function (EmployeRepository $er) {
                    $entreprise = $this->security->getUser()->getEntreprise();

                    return $er
                        ->createQueryBuilder('e')
                        ->where('e.entreprise = :entreprise')
                        ->setParameter('entreprise', $entreprise)
                        ->orderBy('e.id', 'ASC');
                },
                'attr' => [
                    'class' => 'fr-select',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => "Nom de l'agent",
                'placeholder' => "Nom de l'agent",
                'required' => true,
            ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            /** @var Signalement $signalement */
            $signalement = $event->getForm()->getData();
            $signalement->setEntreprise($this->security->getUser()->getEntreprise());
        });
    }
}
