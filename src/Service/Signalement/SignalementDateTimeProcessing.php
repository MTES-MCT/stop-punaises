<?php

namespace App\Service\Signalement;

use App\Entity\Signalement;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

class SignalementDateTimeProcessing
{
    public static function processDateAndTime(FormInterface $form, Signalement $signalement): void
    {
        if (!empty($form->get('punaisesViewedAt')->getData())
            && !empty($form->get('punaisesViewedTimeAt')->getData())
        ) {
            $signalement->setPunaisesViewedAt(
                PunaiseViewedDateFormatter::format(
                    $form->get('punaisesViewedAt')->getData(),
                    $form->get('punaisesViewedTimeAt')->getData()
                )
            );

            $currentDatetime = (new \DateTimeImmutable())
                ->setTimezone(new \DateTimeZone(Signalement::DEFAULT_TIMEZONE))
                ->format('Y-m-d H:i:s');

            if ($form->get('punaisesViewedAt')->getData() == new \DateTimeImmutable('today')
                && $signalement->getPunaisesViewedAt() > new \DateTimeImmutable($currentDatetime)
            ) {
                $form->get('punaisesViewedTimeAt')->addError(new FormError(
                    'L\'heure du jour renseignée n\'est pas encore passée, veuillez renseigner une nouvelle heure.'
                ));
            }
        }
    }
}
