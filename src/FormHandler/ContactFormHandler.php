<?php

namespace App\FormHandler;

use App\Service\Mailer\MailerProvider;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormInterface;

class ContactFormHandler
{
    public function __construct(
        private MailerProvider $mailerProvider,
        private ParameterBagInterface $parameterBag,
    ) {
    }

    public function handle(FormInterface $form)
    {
        $nom = $form->get('nom')->getData();
        $email = $form->get('email')->getData();
        $message = $form->get('message')->getData();

        $this->mailerProvider->sendContactFormMessage(
            adminEmail: $this->parameterBag->get('admin_email'),
            userName: $nom,
            userEmail: $email,
            userMessage: $message,
        );
    }
}
