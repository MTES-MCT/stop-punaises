<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    ini_set('session.gc_maxlifetime', (int) $_ENV['SESSION_MAXLIFETIME']);
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
