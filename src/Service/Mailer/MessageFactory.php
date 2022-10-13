<?php

namespace App\Service\Mailer;

class MessageFactory
{
    public function __construct(private string $from)
    {
    }

    public function createInstanceFrom(Template $template, array $parameters = [])
    {
        return (new Message())
            ->setFrom($this->from)
            ->setTemplate($template)
            ->setParameters($parameters);
    }
}
