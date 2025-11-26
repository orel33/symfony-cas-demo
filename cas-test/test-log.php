<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

// composer require monolog/monolog
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Enable debugging
$logger = new Logger('phpCAS');
$logger->pushHandler(new StreamHandler('/tmp/cas.log', Logger::DEBUG));

// test logging
$logger->debug("Logger OK !");
$logger->info("Logger OK !");
error_log("Logger OK !");

if (!is_writable('/tmp/cas.log')) {
    error_log("cas.log n'est PAS accessible en écriture");
}

// test phpCAS logging
\phpCAS::setLogger($logger);
\phpCAS::log("Logger phpCAS OK !");
error_log("Logger phpCAS OK !");

echo "Test Logger...";

