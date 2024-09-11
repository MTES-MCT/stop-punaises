<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    ini_set('session.gc_maxlifetime', (int) $_ENV['SESSION_MAXLIFETIME']);
    if (null !== $csp = $_SERVER['SECURITY_CSP_HEADER_VALUE'] ?? null) {
        header('Content-Security-Policy: '.$csp);
    }
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
