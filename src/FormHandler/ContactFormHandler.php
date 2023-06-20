<?php

namespace App\FormHandler;

use App\Service\Mailer\MailerProviderInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ContactFormHandler
{
    public function __construct(
        private MailerProviderInterface $mailerProvider,
        private ParameterBagInterface $parameterBag,
    ) {
    }

    public function handle(
        string $nom,
        string $email,
        string $message,
    ) {
        $this->mailerProvider->sendContactFormMessage(
            adminEmail: $this->parameterBag->get('admin_email'),
            userName: $nom,
            userEmail: $email,
            userMessage: $message,
        );
    }
}
