<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

// Enable debugging
phpCAS::setDebug('/tmp/phpcas.log'); // deprecated

// Enable verbose error messages. Disable in production!
\phpCAS::setVerbose(true);

// Client CAS
$server = 'localhost';
$port = 9000;
$service = 'http://localhost:8000/';
\phpCAS::client(CAS_VERSION_3_0, $server, $port, '/cas', $service);

// force baseurl for HTTP CAS server (because only HTTPS is supported)
$baseurl = "http://$server:$port/cas/";   // don't forget trailing slash!
$client = \phpCAS::getCasClient();
$client->setBaseURL($baseurl);

// Disable SSL validation (for testing with local CAS server)
\phpCAS::setNoCasServerValidation();

// Auth
\phpCAS::forceAuthentication();

// logout if desired
if (isset($_REQUEST['logout'])) { phpCAS::logout(); }

?>

<!-- html page -->

<html>

<head>
  <title>Test CAS Docker</title>
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