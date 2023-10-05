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
        if (isset($parameters['lien_entreprises_publiques'])) {
            $parameters['lien_entreprises_publiques'] = $this->baseUrl.$parameters['lien_entreprises_publiques'];
        }

        return (new Message())
            ->setFrom($this->from)
            ->setTemplate($template)
            ->setParameters($parameters);
    }
}
