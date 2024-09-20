<?php

namespace App\EventListener;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class ContentSecurityPolicyListener
{
    public function __construct(
        private ParameterBagInterface $parameterBag,
    ) {
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        $scriptNonce = bin2hex(random_bytes(16));

        $request->attributes->set('csp_script_nonce', $scriptNonce);
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        $response = $event->getResponse();
        $request = $event->getRequest();

        $scriptNonce = $request->attributes->get('csp_script_nonce');

        $cspParameters = $this->parameterBag->get('csp_parameters');

        $csp = 'default-src '.$cspParameters['default-src'].'; '.
                'script-src '.$cspParameters['script-src']." 'nonce-$scriptNonce'; ".
                'style-src '.$cspParameters['style-src'].'; '.
                'style-src-attr '.$cspParameters['style-src-attr'].'; '.
                'img-src '.$cspParameters['img-src'].'; '.
                'connect-src '.$cspParameters['connect-src'].'; '.
                'font-src '.$cspParameters['font-src'].'; '.
                'frame-src '.$cspParameters['frame-src'].'; '.
                'object-src '.$cspParameters['object-src'].'; '.
                'base-uri '.$cspParameters['base-uri'].'; '.
                'form-action '.$cspParameters['form-action'].'; '.
                'frame-ancestors '.$cspParameters['frame-ancestors'].'; '.
                'media-src '.$cspParameters['media-src'];

        if ($this->parameterBag->get('csp_enable')) {
            $response->headers->set('Content-Security-Policy', $csp);
        }
    }
}
