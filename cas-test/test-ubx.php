<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Enable debugging
$logger = new Logger('phpCAS');
$logger->pushHandler(new StreamHandler('/var/log/phpcas/cas.log', Logger::DEBUG));
\phpCAS::setLogger($logger);

// Enable verbose error messages. Disable in production!
\phpCAS::setVerbose(true);

// Client CAS
$server = 'cas.u-bordeaux.fr';
$port = 443;
$service = 'https://promo-st.emi.u-bordeaux.fr/';
\phpCAS::client(CAS_VERSION_3_0, $server, $port, '/cas', $service);

// Enable SSL validation (Geant CA Cert)
\phpCAS::setCasServerCACert('/etc/ssl/certs/ca-certificates.crt');

// Auth
\phpCAS::forceAuthentication();

// logout if desired
if (isset($_REQUEST['logout'])) { phpCAS::logout(); }

// for debug purposes, print the session array
// echo "<pre>";
// echo "Session data:\n";
// print_r($_SESSION['phpCAS']);
// echo "</pre>";

?>

<!-- html page -->

<html>
  <head>
    <title>Test CAS UBx</title>
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

