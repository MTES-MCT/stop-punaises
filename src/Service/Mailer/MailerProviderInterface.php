<?php

namespace App\Service\Mailer;

interface MailerProviderInterface
{
    public function send(MessageInterface $message);
}
