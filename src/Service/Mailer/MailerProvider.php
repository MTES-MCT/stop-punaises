<?php

namespace App\Service\Mailer;

use App\Entity\Intervention;
use App\Entity\Signalement;
use App\Entity\User;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MailerProvider implements MailerProviderInterface
{
    public function __construct(
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private MessageFactory $messageFactory,
        private ParameterBagInterface $parameterBag,
    ) {
    }

    public function send(MessageInterface $message): void
    {
        /** @var Message $message */
        $message = $message;
        if (empty($message->getTo())) {
            $message->setTo([$this->parameterBag->get('inconnu_email')]);
        }

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

    public function sendContactFormMessage(string $adminEmail, string $userName, string $userEmail, string $userMessage): void
    {
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::ADMIN_CONTACT, [
                'userName' => $userName,
                'userEmail' => $userEmail,
                'userMessage' => $userMessage,
            ])
            ->setTo([$adminEmail]);

        $this->send($message);
    }

    public function sendResetPasswordMessage(User $user): void
    {
        $link = $this->urlGenerator->generate('activate_account', ['uuid' => $user->getUuid(), 'token' => $user->getToken()]);
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::RESET_PASSWORD, ['link' => $link])
            ->setTo([$user->getEmail()]);

        $this->send($message);
    }

    public function sendActivateMessage(User $user): void
    {
        $link = $this->urlGenerator->generate('activate_account', ['uuid' => $user->getUuid(), 'token' => $user->getToken()]);
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::ACCOUNT_ACTIVATION, ['link' => $link])
            ->setTo([$user->getEmail()]);

        $this->send($message);
    }

    public function sendSignalementValidationWithPro(Signalement $signalement): void
    {
        $emailOccupant = $signalement->getEmailOccupant();
        $link = $this->urlGenerator->generate('app_suivi_usager_view', ['uuidPublic' => $signalement->getUuidPublic()]);
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::SIGNALEMENT_PROFESSIONAL, [
                'nom_usager' => $signalement->getNomCompletOccupant(),
                'adresse' => $signalement->getAdresseComplete(),
                'link' => $link,
            ])
            ->setTo([$emailOccupant]);

        $this->send($message);
    }

    public function sendSignalementValidationWithAutotraitement(Signalement $signalement): void
    {
        $emailOccupant = $signalement->getEmailOccupant();
        $link = $this->urlGenerator->generate('app_suivi_usager_view', ['uuidPublic' => $signalement->getUuidPublic()]);
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::SIGNALEMENT_AUTO, [
                'nom_usager' => $signalement->getNomCompletOccupant(),
                'link' => $link,
            ])
            ->setTo([$emailOccupant]);

        $this->send($message);
    }

    public function sendSignalementValidationWithConseilsEviterPunaises(Signalement $signalement): void
    {
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::SIGNALEMENT_CONSEILS_EVITER_PUNAISES, [
                'nom_usager' => $signalement->getNomCompletDeclarant(),
                'link' => '/build/'.$this->parameterBag->get('doc_conseil'),
            ])
            ->setTo([$signalement->getEmailDeclarant()]);

        $this->send($message);
    }

    public function sendSignalementValidationWithEntreprisesPubliques(Signalement $signalement): void
    {
        $emailOccupant = $signalement->getEmailOccupant();
        $link = $this->urlGenerator->generate('app_suivi_usager_view', ['uuidPublic' => $signalement->getUuidPublic()]);
        $linkEntreprisesPubliques = $this->urlGenerator->generate('app_front_entreprises_labellisees');
        $linkEntreprisesPubliques .= '?code-postal='.$signalement->getCodePostal();
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::SIGNALEMENT_ENTREPRISES_LABELLISEES, [
                'nom_usager' => $signalement->getNomCompletOccupant(),
                'link' => $link,
                'link_2' => $linkEntreprisesPubliques,
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
                'link' => $link,
            ])
            ->setTo([$emailEntreprise]);

        $this->send($message);
    }

    public function sendSignalementNewEstimation(Signalement $signalement, Intervention $intervention): void
    {
        $emailOccupant = $signalement->getEmailOccupant();
        $link = $this->urlGenerator->generate('app_suivi_usager_view', ['uuidPublic' => $signalement->getUuidPublic()]);
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::SIGNALEMENT_NEW_ESTIMATION, [
                'nom_usager' => $signalement->getNomCompletOccupant(),
                'nom_entreprise' => $intervention->getEntreprise()->getNom(),
                'reference' => $signalement->getReference(),
                'link' => $link,
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
                'link' => $link,
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
                'link' => $link,
            ])
            ->setTo([$emailEntreprise]);

        $this->send($message);
    }

    public function sendInterventionCanceled(Signalement $signalement, string $nomEntreprise): void
    {
        $link = $this->urlGenerator->generate('app_suivi_usager_view', ['uuidPublic' => $signalement->getUuidPublic()]);
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::SIGNALEMENT_INTERVENTION_CANCELED, [
                'nom_usager' => $signalement->getNomCompletOccupant(),
                'nom_entreprise' => $nomEntreprise,
                'link' => $link,
            ])
            ->setTo([$signalement->getEmailOccupant()]);

        $this->send($message);
    }

    public function sendNotificationToUsager(Signalement $signalement, string $nomEntreprise): void
    {
        $link = $this->urlGenerator->generate('app_suivi_usager_view', ['uuidPublic' => $signalement->getUuidPublic()]);
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::SIGNALEMENT_NEW_MESSAGE, [
                'nom_usager' => $signalement->getNomCompletOccupant(),
                'nom_entreprise' => $nomEntreprise,
                'link' => $link,
            ])
            ->setTo([$signalement->getEmailOccupant()]);

        $this->send($message);
    }

    public function sendNotificationToEntreprise(Signalement $signalement, string $emailEntreprise): void
    {
        $link = $this->urlGenerator->generate('app_signalement_view', ['uuid' => $signalement->getUuid()]);
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::SIGNALEMENT_NEW_MESSAGE_FOR_PRO, [
                'reference' => $signalement->getReference(),
                'link' => $link,
            ])
            ->setTo([$emailEntreprise]);

        $this->send($message);
    }

    public function sendSignalementTraitementResolved(Signalement $signalement, Intervention $intervention): void
    {
        $emailOccupant = $signalement->getEmailOccupant();
        $link = $this->urlGenerator->generate('app_suivi_usager_view', ['uuidPublic' => $signalement->getUuidPublic()]);
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::SIGNALEMENT_TRAITEMENT_RESOLVED, [
                'nom_usager' => $signalement->getNomCompletOccupant(),
                'nom_entreprise' => $intervention->getEntreprise()->getNom(),
                'link' => $link,
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
                'link' => $link,
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
                'link' => $link,
            ])
            ->setTo([$emailEntreprise]);

        $this->send($message);
    }

    public function sendSignalementClosedByWebsite(Signalement $signalement): void
    {
        $emailOccupant = $signalement->getEmailOccupant();
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::SIGNALEMENT_CLOSED_AUTO, [
                'nom_usager' => $signalement->getNomCompletOccupant(),
            ])
            ->setTo([$emailOccupant]);

        $this->send($message);
    }

    public function sendSignalementSuiviTraitementPro(Intervention $intervention): void
    {
        $signalement = $intervention->getSignalement();
        $emailOccupant = $signalement->getEmailOccupant();
        $link = $this->urlGenerator->generate('app_suivi_usager_view', ['uuidPublic' => $signalement->getUuidPublic()]);
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::SIGNALEMENT_SUIVI_TRAITEMENT_PRO, [
                'nom_usager' => $signalement->getNomCompletOccupant(),
                'nom_entreprise' => $intervention->getEntreprise()->getNom(),
                'link' => $link,
            ])
            ->setTo([$emailOccupant]);

        $this->send($message);
    }

    public function sendSignalementSuiviTraitementProForPro(Intervention $intervention): void
    {
        $emailEntreprise = $intervention->getEntreprise()->getUser()->getEmail();
        $signalement = $intervention->getSignalement();
        $link = $this->urlGenerator->generate('app_signalement_view', ['uuid' => $signalement->getUuid()]);
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::SIGNALEMENT_SUIVI_TRAITEMENT_PRO_FOR_PRO, [
                'reference' => $signalement->getReference(),
                'link' => $link,
            ])
            ->setTo([$emailEntreprise]);

        $this->send($message);
    }

    public function sendSignalementSuiviTraitementAuto(Signalement $signalement): void
    {
        $emailOccupant = $signalement->getEmailOccupant();
        $link = $this->urlGenerator->generate('app_suivi_usager_view', ['uuidPublic' => $signalement->getUuidPublic()]);
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::SIGNALEMENT_SUIVI_TRAITEMENT_AUTO, [
                'nom_usager' => $signalement->getNomCompletOccupant(),
                'date' => $signalement->getCreatedAt()->format('d/m/Y'),
                'link' => $link,
            ])
            ->setTo([$emailOccupant]);

        $this->send($message);
    }

    public function sendSignalementWithNoMoreEntreprise(Signalement $signalement): void
    {
        $emailOccupant = $signalement->getEmailOccupant();
        $link = $this->urlGenerator->generate('app_suivi_usager_view', ['uuidPublic' => $signalement->getUuidPublic()]);
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::SIGNALEMENT_NO_MORE_ENTREPRISES, [
                'nom_usager' => $signalement->getNomCompletOccupant(),
                'link' => $link,
            ])
            ->setTo([$emailOccupant]);

        $this->send($message);
    }

    public function sendAdminToujoursPunaises($email, Signalement $signalement): void
    {
        $link = $this->urlGenerator->generate('app_signalement_view', ['uuid' => $signalement->getUuid()]);
        $message = $this
            ->messageFactory
            ->createInstanceFrom(Template::ADMIN_TOUJOURS_PUNAISES, [
                'reference' => $signalement->getReference(),
                'link' => $link,
            ])
            ->setTo([$email]);

        $this->send($message);
    }
}
