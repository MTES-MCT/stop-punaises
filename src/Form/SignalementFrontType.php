<?php

namespace App\Form;

use App\Entity\Signalement;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SignalementFrontType extends AbstractType
{
    public function __construct(private Security $security)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Step info_logement
            ->add('typeLogement', ChoiceType::class, [
                'attr' => [
                    'class' => 'fr-radio',
                ],
                'choices' => [
                    'Un appartement' => 'appartement',
                    'Une maison' => 'maison',
                    'Autre' => 'autre',
                ],
                'expanded' => true,
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Je vis dans...',
                'row_attr' => [
                    'class' => 'fr-select-group',
                ],
                'required' => true,
            ])
            ->add('superficie', TextType::class, [
                'attr' => [
                    'class' => 'fr-input',
                    'placeholder' => '36',
                    'maxlength' => '10',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'La superficie de mon logement est de...',
                'required' => true,
            ])
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
            ->add('geoloc', HiddenType::class, [
                'attr' => [
                    'class' => 'fr-hidden',
                ],
                'required' => false,
            ])

            // Step info_locataire
            ->add('locataire', ChoiceType::class, [
                'attr' => [
                    'class' => 'fr-radio',
                ],
                'choices' => [
                    'Propriétaire' => false,
                    'Locataire' => true,
                ],
                'expanded' => true,
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Je suis...',
                'row_attr' => [
                    'class' => 'fr-select-group',
                ],
                'required' => true,
            ])
            ->add('nomProprietaire', TextType::class, [
                'attr' => [
                    'class' => 'fr-input',
                    'maxlength' => '100',
                    'placeholder' => 'Nom du bailleur',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Le nom de mon bailleur / propriétaire est',
            ])
            ->add('logementSocial', ChoiceType::class, [
                'attr' => [
                    'class' => 'fr-radio',
                ],
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'expanded' => true,
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Je vis dans un logement social',
                'row_attr' => [
                    'class' => 'fr-select-group',
                ],
                'required' => true,
            ])
            ->add('allocataire', ChoiceType::class, [
                'attr' => [
                    'class' => 'fr-radio',
                ],
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'expanded' => true,
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Je touche une allocation logement',
                'row_attr' => [
                    'class' => 'fr-select-group',
                ],
                'required' => true,
            ])
            ->add('numeroAllocataire', TextType::class, [
                'attr' => [
                    'class' => 'fr-input',
                    'maxlength' => '50',
                    'placeholder' => '012345',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Mon numéro d\'allocataire est',
            ])

            // Step info_problemes
            ->add('dureeInfestation', ChoiceType::class, [
                'attr' => [
                    'class' => 'fr-radio',
                ],
                'choices' => [
                    'Moins de 3 mois' => 'LESS-3-MONTHS',
                    'Entre 3 et 6 mois' => '3-6-MONTHS',
                    'Plus de 6 mois' => 'MORE-6-MONTHS',
                ],
                'expanded' => true,
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Je pense avoir un problème de punaises depuis...',
                'row_attr' => [
                    'class' => 'fr-select-group',
                ],
                'required' => true,
            ])
            ->add('infestationLogementsVoisins', ChoiceType::class, [
                'attr' => [
                    'class' => 'fr-radio',
                ],
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                    'Je ne sais pas' => null,
                ],
                'expanded' => true,
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'L\'infestation concerne aussi les logements voisins',
                'row_attr' => [
                    'class' => 'fr-select-group',
                ],
                'required' => true,
            ])

            // Step traces_punaises_piqures
            ->add('piquresExistantes', ChoiceType::class, [
                'attr' => [
                    'class' => 'fr-radio',
                ],
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'expanded' => true,
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'J\'ai des piqures de punaises de lit',
                'row_attr' => [
                    'class' => 'fr-select-group',
                ],
                'required' => true,
            ])
            ->add('piquresConfirmees', ChoiceType::class, [
                'attr' => [
                    'class' => 'fr-radio',
                ],
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'expanded' => true,
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Un ou une professionnelle de santé a confirmé que ce sont bien des piqures de punaises de lit',
                'row_attr' => [
                    'class' => 'fr-select-group',
                ],
                'required' => true,
            ])

            // Step info_usager
            ->add('niveauInfestation', HiddenType::class, [
                'attr' => [
                    'class' => 'fr-hidden',
                    'pattern' => '[0-9]',
                ],
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
                    'maxlength' => '15',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Téléphone',
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
                'label' => 'Email',
                'required' => false,
            ])
            ->add('autotraitement', HiddenType::class, [
                'attr' => [
                    'class' => 'fr-hidden',
                ],
                'required' => true,
                'empty_data' => false,
            ])
        ;
        $builder->get('geoloc')->addModelTransformer(new CallbackTransformer(
            function ($tagsAsArray) {
                // transform the array to a string
                if (!empty($tagsAsArray)) {
                    return $tagsAsArray[0].'|'.$tagsAsArray[1];
                }

                return '';
            },
            function ($tagsAsString) {
                // transform the string back to an array
                if (!empty($tagsAsString)) {
                    $coord = explode('|', $tagsAsString);

                    return ['lat' => $coord[0], 'lng' => $coord[1]];
                }

                return [];
            }
        ));

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            /** @var Signalement $signalement */
            $signalement = $event->getData();
            $form = $event->getForm();

            $extraData = $form->getExtraData();

            $dejectionsDetails = $this->getDejectionDetails($extraData);
            if (!empty($dejectionsDetails)) {
                $signalement->setDejectionsDetails($dejectionsDetails);
            }
            $oeufsEtLarvesDetails = $this->getOeufsEtLarvesDetails($extraData);
            if (!empty($oeufsEtLarvesDetails)) {
                $signalement->setOeufsEtLarvesDetails($oeufsEtLarvesDetails);
            }
            $punaisesDetails = $this->getPunaisesDetails($extraData);
            if (!empty($punaisesDetails)) {
                $signalement->setPunaisesDetails($punaisesDetails);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Signalement::class,
            'allow_extra_fields' => true,
            'csrf_protection' => false,
            'validation_groups' => ['Default', 'front_add_signalement_logement'],
        ]);
    }

    private function getDejectionDetails(array $data): array
    {
        $dejectionsDetails = [];
        if (!empty($data['dejectionsTrouvees'])) {
            $dejectionsDetails = [
                'dejectionsTrouvees' => $data['dejectionsTrouvees'],
            ];
            if ('true' == $data['dejectionsTrouvees']) {
                $dejectionsDetails['dejectionsNombrePiecesConcernees'] = $data['dejectionsNombrePiecesConcernees'] ?? null;
                $dejectionsDetails['dejectionsFaciliteDetections'] = $data['dejectionsFaciliteDetections'] ?? null;
                $dejectionsDetails['dejectionsLieuxObservations'] = $data['dejectionsLieuxObservations'] ?? null;
            }
        }

        return $dejectionsDetails;
    }

    private function getOeufsEtLarvesDetails(array $data): array
    {
        $oeufsEtLarvesDetails = [];
        if (!empty($data['oeufsEtLarvesTrouves'])) {
            $oeufsEtLarvesDetails = [
                'oeufsEtLarvesTrouves' => $data['oeufsEtLarvesTrouves'],
            ];
            if ('true' == $data['oeufsEtLarvesTrouves']) {
                $oeufsEtLarvesDetails['oeufsEtLarvesNombrePiecesConcernees'] = $data['oeufsEtLarvesNombrePiecesConcernees'] ?? null;
                $oeufsEtLarvesDetails['oeufsEtLarvesFaciliteDetections'] = $data['oeufsEtLarvesFaciliteDetections'] ?? null;
                $oeufsEtLarvesDetails['oeufsEtLarvesLieuxObservations'] = $data['oeufsEtLarvesLieuxObservations'] ?? null;
            }
        }

        return $oeufsEtLarvesDetails;
    }

    private function getPunaisesDetails(array $data): array
    {
        $punaisesDetails = [];
        if (!empty($data['punaisesTrouvees'])) {
            $punaisesDetails = [
                'punaisesTrouvees' => $data['punaisesTrouvees'],
            ];
            if ('true' == $data['punaisesTrouvees']) {
                $punaisesDetails['punaisesNombrePiecesConcernees'] = $data['punaisesNombrePiecesConcernees'] ?? null;
                $punaisesDetails['punaisesFaciliteDetections'] = $data['punaisesFaciliteDetections'] ?? null;
                $punaisesDetails['punaisesLieuxObservations'] = $data['punaisesLieuxObservations'] ?? null;
            }
        }

        return $punaisesDetails;
    }
}
