<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Enable verbose error messages. Disable in production!
\phpCAS::setVerbose(true);

// Enable debugging
$logger = new Logger('phpCAS');
$logger->pushHandler(new StreamHandler('/var/log/phpcas/cas.log', Logger::DEBUG));
\phpCAS::setLogger($logger);

// Client CAS
$service = 'https://promo-st.emi.u-bordeaux.fr/';
\phpCAS::client(CAS_VERSION_3_0, 'cas.u-bordeaux.fr', 443, '/cas', $service);

// SSL validation
// \phpCAS::setNoCasServerValidation(); // accept self-signed certificates (local CAS only)
// \phpCAS::setCasServerCACert(__DIR__ . '/geant-ca.crt');
\phpCAS::setCasServerCACert('/etc/ssl/certs/ca-certificates.crt');

// Auth
\phpCAS::forceAuthentication();

// logout if desired
if (isset($_REQUEST['logout'])) {
        phpCAS::logout();
}

// for this test, simply print that the authentication was successfull
?>

<html>
  <head>
    <title>Cas Test</title>
  </head>
  <body>
    <h1>Successfull Authentication!</h1>
    <p>Version <b><?php echo phpCAS::getVersion(); ?></b>.</p>
    <p>User: <b><?php echo phpCAS::getUser(); ?></b>.</p>
    <p>Attributes: <b><?php print_r(phpCAS::getAttributes()); ?></b>.</p>
    <br>
    <p><a href="?logout=">Logout</a></p>
  </body>
</html>
