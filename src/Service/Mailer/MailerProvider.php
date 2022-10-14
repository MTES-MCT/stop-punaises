<?php

namespace App\Service\Mailer;

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
}
