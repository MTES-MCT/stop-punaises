<?php

namespace App\Service\Mailer;

use App\Entity\Intervention;
use App\Entity\Signalement;
use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MailerProvider implements MailerProviderInterface
{
    public function __construct(private MailerInterface $mailer,
                                private UrlGeneratorInterface $urlGenerator,
                                private MessageFactory $messageFactory)
    {
    }

    public function send(MessageInterface $message): void
    {
        $email = (new Email())
            ->from($message->getFrom())
            ->to(...$message->getTo())
            ->text('');

        $email->getHeaders()
            ->addTextHeader('templateId', $message->getTemplate()->value)
            ->addParameterizedHeader('params', 'params', $message->getParameters());

        $this->mailer->send($email);
    }

    public function sendMessage(Template $template, User $user): void
    {
        $message = $this
            ->messageFactory
            ->createInstanceFrom($template)
            ->setTo([$user->getEmail()]);

        $this->send($message);
    }

    public function sendResetPasswordMessage(User $user): void
    {
        $link = $this->urlGenerator->generate('reset_password', ['token' => $user->getToken()]);
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::RESET_PASSWORD, ['link' => $link])
            ->setTo([$user->getEmail()]);

        $this->send($message);
    }

    public function sendActivateMessage(User $user): void
    {
        $link = $this->urlGenerator->generate('activate_account', ['token' => $user->getToken()]);
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::ACCOUNT_ACTIVATION, ['link' => $link])
            ->setTo([$user->getEmail()]);

        $this->send($message);
    }

    public function sendSignalementValidationWithPro(Signalement $signalement): void
    {
        $emailOccupant = $signalement->getEmailOccupant();
        $link = $this->urlGenerator->generate('app_suivi_usager_view', ['uuid' => $signalement->getUuid()]);
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::SIGNALEMENT_PROFESSIONAL, [
                'nom_usager' => $signalement->getNomCompletOccupant(),
                'adresse' => $signalement->getAdresseComplete(),
                'lien' => $link,
            ])
            ->setTo([$emailOccupant]);

        $this->send($message);
    }

    public function sendSignalementValidationWithAutotraitement(Signalement $signalement, string $linkToPdf): void
    {
        $emailOccupant = $signalement->getEmailOccupant();
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::SIGNALEMENT_AUTO, [
                'nom_usager' => $signalement->getNomCompletOccupant(),
                'lien_pdf' => $linkToPdf,
            ])
            ->setTo([$emailOccupant]);

        $this->send($message);
    }

    public function sendSignalementNewForPro(string $emailEntreprise, Signalement $signalement): void
    {
        $link = $this->urlGenerator->generate('app_signalement_view', ['uuid' => $signalement->getUuid()]);
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::SIGNALEMENT_NEW_FOR_PRO, [
                'lien' => $link,
            ])
            ->setTo([$emailEntreprise]);

        $this->send($message);
    }

    public function sendSignalementNewEstimation(Signalement $signalement, Intervention $intervention): void
    {
        $emailOccupant = $signalement->getEmailOccupant();
        $link = $this->urlGenerator->generate('app_suivi_usager_view', ['uuid' => $signalement->getUuid()]);
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::SIGNALEMENT_NEW_ESTIMATION, [
                'nom_usager' => $signalement->getNomCompletOccupant(),
                'nom_entreprise' => $intervention->getEntreprise()->getNom(),
                'reference' => $signalement->getReference(),
                'lien' => $link,
            ])
            ->setTo([$emailOccupant]);

        $this->send($message);
    }

    public function sendSignalementEstimationAccepted(string $emailEntreprise, Signalement $signalement): void
    {
        $link = $this->urlGenerator->generate('app_signalement_view', ['uuid' => $signalement->getUuid()]);
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::SIGNALEMENT_ESTIMATION_ACCEPTED, [
                'reference' => $signalement->getReference(),
                'lien' => $link,
            ])
            ->setTo([$emailEntreprise]);

        $this->send($message);
    }

    public function sendSignalementEstimationRefused(string $emailEntreprise, Signalement $signalement): void
    {
        $link = $this->urlGenerator->generate('app_signalement_view', ['uuid' => $signalement->getUuid()]);
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::SIGNALEMENT_ESTIMATION_REFUSED, [
                'reference' => $signalement->getReference(),
                'lien' => $link,
            ])
            ->setTo([$emailEntreprise]);

        $this->send($message);
    }

    public function sendNotificationToUsager(Signalement $signalement, string $nomEntreprise): void
    {
        $link = $this->urlGenerator->generate('app_suivi_usager_view', ['uuid' => $signalement->getUuid()]);
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::SIGNALEMENT_NEW_MESSAGE, [
                'nom_usager' => $signalement->getNomCompletOccupant(),
                'nom_entreprise' => $nomEntreprise,
                'lien' => $link,
            ])
            ->setTo([$signalement->getEmailOccupant()]);

        $this->send($message);
    }

    public function sendSignalementNewMessageForPro(string $emailEntreprise, Signalement $signalement): void
    {
        $link = $this->urlGenerator->generate('app_signalement_view', ['uuid' => $signalement->getUuid()]);
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::SIGNALEMENT_NEW_MESSAGE_FOR_PRO, [
                'reference' => $signalement->getReference(),
                'lien' => $link,
            ])
            ->setTo([$emailEntreprise]);

        $this->send($message);
    }

    public function sendSignalementTraitementResolved(Signalement $signalement, Intervention $intervention): void
    {
        $emailOccupant = $signalement->getEmailOccupant();
        $link = $this->urlGenerator->generate('app_suivi_usager_view', ['uuid' => $signalement->getUuid()]);
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::SIGNALEMENT_TRAITEMENT_RESOLVED, [
                'nom_usager' => $signalement->getNomCompletOccupant(),
                'nom_entreprise' => $intervention->getEntreprise()->getNom(),
                'lien' => $link,
            ])
            ->setTo([$emailOccupant]);

        $this->send($message);
    }

    public function sendSignalementTraitementResolvedForPro(string $emailEntreprise, Signalement $signalement): void
    {
        $link = $this->urlGenerator->generate('app_signalement_view', ['uuid' => $signalement->getUuid()]);
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::SIGNALEMENT_TRAITEMENT_RESOLVED_FOR_PRO, [
                'reference' => $signalement->getReference(),
                'lien' => $link,
            ])
            ->setTo([$emailEntreprise]);

        $this->send($message);
    }

    public function sendSignalementClosed(string $emailEntreprise, Signalement $signalement): void
    {
        $link = $this->urlGenerator->generate('app_signalement_view', ['uuid' => $signalement->getUuid()]);
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::SIGNALEMENT_CLOSED, [
                'reference' => $signalement->getReference(),
                'lien' => $link,
            ])
            ->setTo([$emailEntreprise]);

        $this->send($message);
    }

    public function sendSignalementSuiviTraitementPro(Signalement $signalement, Intervention $intervention): void
    {
        $emailOccupant = $signalement->getEmailOccupant();
        $link = $this->urlGenerator->generate('app_suivi_usager_view', ['uuid' => $signalement->getUuid()]);
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::SIGNALEMENT_SUIVI_TRAITEMENT_PRO, [
                'nom_usager' => $signalement->getNomCompletOccupant(),
                'nom_entreprise' => $intervention->getEntreprise()->getNom(),
                'lien' => $link,
            ])
            ->setTo([$emailOccupant]);

        $this->send($message);
    }

    public function sendSignalementSuiviTraitementAuto(Signalement $signalement): void
    {
        $emailOccupant = $signalement->getEmailOccupant();
        $link = $this->urlGenerator->generate('app_suivi_usager_view', ['uuid' => $signalement->getUuid()]);
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::SIGNALEMENT_SUIVI_TRAITEMENT_PRO, [
                'nom_usager' => $signalement->getNomCompletOccupant(),
                'date' => $signalement->getCreatedAt()->format('d/m/Y'),
                'lien' => $link,
            ])
            ->setTo([$emailOccupant]);

        $this->send($message);
    }

    public function sendSignalementWithNoMoreEntreprise(Signalement $signalement): void
    {
        $emailOccupant = $signalement->getEmailOccupant();
        $link = $this->urlGenerator->generate('app_suivi_usager_view', ['uuid' => $signalement->getUuid()]);
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::SIGNALEMENT_NO_MORE_ENTREPRISES, [
                'nom_usager' => $signalement->getNomCompletOccupant(),
                'lien' => $link,
            ])
            ->setTo([$emailOccupant]);

        $this->send($message);
    }

    public function sendAdminToujoursPunaises($email, Signalement $signalement): void
    {
        $link = $this->urlGenerator->generate('app_suivi_usager_view', ['uuid' => $signalement->getUuid()]);
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::ADMIN_TOUJOURS_PUNAISES, [
                'reference' => $signalement->getReference(),
                'lien' => $link,
            ])
            ->setTo([$email]);

        $this->send($message);
    }
}
