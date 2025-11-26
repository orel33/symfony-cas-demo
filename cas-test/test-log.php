<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
error_log("Start Test Logger.");

require_once __DIR__ . '/vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;


$logger = new Logger('phpCAS');
$logger->pushHandler(new StreamHandler('/var/log/phpcas/cas.log', Logger::DEBUG));
$logger->debug("test logger debug!");
$logger->info("test loger info!");

\phpCAS::setLogger($logger);
\phpCAS::log("test logger phpCAS!");


// Enable debugging
$logger = new Logger('phpCAS');
$logger->pushHandler(new StreamHandler('/var/log/phpcas/cas.log', Logger::DEBUG));

// test logging
$logger->debug("Logger OK !");
$logger->info("Logger OK !");


// test phpCAS logging
\phpCAS::setLogger($logger);
\phpCAS::log("Logger phpCAS OK !");
error_log("Logger phpCAS OK !");

echo "Test Logger: done";

