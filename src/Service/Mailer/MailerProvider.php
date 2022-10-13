<?php

namespace App\Service\Mailer;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerProvider implements MailerProviderInterface
{
    public function __construct(private MailerInterface $mailer)
    {
    }

    public function send(MessageInterface $message)
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
}
