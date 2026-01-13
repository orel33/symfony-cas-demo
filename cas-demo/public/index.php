<?php

use App\Kernel;
// use Symfony\Component\ErrorHandler\Debug;
// use Symfony\Component\HttpFoundation\Request;

// enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
    // $kernel = new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
    // return $kernel->handle(Request::createFromGlobals());
};

