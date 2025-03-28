<?php

namespace App\Form;

use App\Entity\Enum\Declarant;
use App\Entity\Enum\PlaceType;
use App\Entity\Enum\SignalementType;
use App\Entity\Signalement;
use App\Repository\TerritoireRepository;
use App\Service\Signalement\ReferenceGenerator;
use App\Service\Signalement\SignalementDateTimeProcessing;
use App\Service\Signalement\ZipCodeProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class SignalementErpType extends AbstractType
{
    public function __construct(
        private ZipCodeProvider $zipCodeProvider,
        private ReferenceGenerator $referenceGenerator,
        private TerritoireRepository $territoireRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('punaisesViewedAt', DateType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'attr' => [
                    'class' => 'fr-input',
                ],
                'row_attr' => [
                    'class' => 'fr-input-group',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Date',
                'required' => true,
                'invalid_message' => 'La date que vous avez saisie a un format de date incorrect.',
            ])
            ->add('punaisesViewedTimeAt', TimeType::class, [
                'widget' => 'single_text',
                'mapped' => false,
                'attr' => [
                    'class' => 'fr-input',
                ],
                'row_attr' => [
                    'class' => 'fr-input-group',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Heure',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(message: 'Veuillez renseigner l\'heure.'),
                ],
            ])
            ->add('nomProprietaire', TextType::class, [
                'attr' => [
                    'class' => 'fr-input',
                    'maxlength' => '100',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Nom de l\'établissement',
                'required' => true,
            ])
            ->add('adresse', TextType::class, [
                'attr' => [
                    'class' => 'fr-input',
                    'maxlength' => '100',
                    'readonly' => true,
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Adresse',
                'help' => 'Tapez l\'adresse puis selectionnez-la dans la liste.',
                'help_attr' => [
                    'class' => 'fr-hint-text',
                ],
                'required' => true,
            ])
            ->add('codePostal', TextType::class, [
                'attr' => [
                    'class' => 'fr-input',
                    'pattern' => '[0-9]{5}',
                    'maxlength' => '5',
                    'minlength' => '5',
                    'readonly' => true,
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Code postal',
                'required' => true,
            ])
            ->add('ville', TextType::class, [
                'attr' => [
                    'class' => 'fr-input',
                    'maxlength' => '100',
                    'readonly' => true,
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Ville',
                'required' => true,
            ])
            ->add('codeInsee', HiddenType::class, [
                'attr' => [
                    'class' => 'fr-hidden',
                ],
                'required' => false,
            ])
            ->add('geoloc', HiddenType::class, [
                'attr' => [
                    'class' => 'fr-hidden',
                ],
                'required' => false,
                'mapped' => false,
            ])
            ->add('placeType', EnumType::class, [
                'class' => PlaceType::class,
                'choices' => PlaceType::getErpTypes(),
                'choice_label' => function ($value) {
                    return $value->label();
                },
                'choice_attr' => [
                    'class' => 'fr-radio',
                ],
                'expanded' => true,
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Type d\'établissement',
                'required' => true,
            ])
            ->add('isPlaceAvertie', ChoiceType::class, [
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
                'label' => 'Avez-vous prévenu l\'établissement ?',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(message: 'Veuillez indiquer si vous avez prévenu l\'établissement.'),
                ],
            ])
            ->add('autresInformations', TextareaType::class, [
                'attr' => [
                    'class' => 'fr-input',
                    'minlength' => 10,
                    'maxlength' => 500,
                    'rows' => 5,
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Autres informations (facultatif)',
                'help' => 'Ajouter les informations que vous jugez utiles à votre signalement (500 caractères maximum).',
                'help_attr' => [
                    'class' => 'fr-hint-text',
                ],
                'required' => false,
            ])
            ->add('nomDeclarant', TextType::class, [
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
            ->add('prenomDeclarant', TextType::class, [
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
            ->add('emailDeclarant', EmailType::class, [
                'attr' => [
                    'class' => 'fr-input',
                ],
                'label_attr' => [
                    'class' => 'fr-label',
                ],
                'label' => 'Adresse email',
                'required' => true,
            ]);

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            /** @var Signalement $signalement */
            $signalement = $event->getData();

            $territory = $this->territoireRepository->findOneBy([
                'zip' => $this->zipCodeProvider->getByCodePostal($signalement->getCodePostal()),
            ]);

            $signalement
                ->setTerritoire($territory)
                ->setReference($this->referenceGenerator->generate())
                ->setDeclarant(Declarant::DECLARANT_USAGER)
                ->setType(SignalementType::TYPE_ERP);

            SignalementDateTimeProcessing::processDateAndTime($form, $signalement);

            $event->setData($signalement);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Signalement::class,
            'validation_groups' => ['Default', 'front_add_signalement_erp'],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'signalement_front';
    }
}
