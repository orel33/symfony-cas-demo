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
$server = 'cas-test.u-bordeaux.fr';
$port = 443;
$service = 'https://promo-st.emi.u-bordeaux.fr/';
\phpCAS::client(CAS_VERSION_3_0, $server, $port, '/cas', $service);

// Enable SSL validation (Geant CA Cert)
\phpCAS::setCasServerCACert('/etc/ssl/certs/ca-certificates.crt');

// Auth
\phpCAS::forceAuthentication();

// logout if desired
if (isset($_REQUEST['logout'])) { phpCAS::logout(); }

?>

<!-- html page -->

<html>
  <head>
    <title>Test CAS UBx</title>
  </head>
  <body>
    <h1>Test CAS UBx</h1>
    <p>Server CAS: <?php echo $server; ?> (port <?php echo $port; ?>)</p>
    <p>Service: <?php echo $service; ?></p>
    <h2>Authentication success!</h2>
    <p>User: <?php echo phpCAS::getUser(); ?></p>
    <p>Mail: <?php echo phpCAS::getAttribute('mail'); ?></p>
    <p>Name: <?php echo phpCAS::getAttribute('displayName'); ?></p>
    <br>
    <p>Attributes: <pre><?php print_r(phpCAS::getAttributes()); ?></pre></p>
    <br>
    <p><a href="?logout=">Logout</a></p>
  </body>
</html>

