<?php

namespace App\Service\Mailer;

class MessageFactory
{
    public function __construct(private string $baseUrl, private string $from)
    {
    }

    public function createInstanceFrom(Template $template, array $parameters = [])
    {
        if (isset($parameters['link'])) {
            $parameters['link'] = $this->baseUrl.$parameters['link'];
        }
        if (isset($parameters['link_2'])) {
            $parameters['link_2'] = $this->baseUrl.$parameters['link_2'];
        }

        return (new Message())
            ->setFrom($this->from)
            ->setTemplate($template)
            ->setParameters($parameters);
    }
}
