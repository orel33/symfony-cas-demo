<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';


// Enable debugging
phpCAS::setLogger();
// Enable verbose error messages. Disable in production!
phpCAS::setVerbose(true);

// Client CAS
$service = 'https://promo-st.emi.u-bordeaux.fr/';
\phpCAS::client(CAS_VERSION_3_0, 'cas.u-bordeaux.fr', 443, '/cas', $service);

// SSL validation
// \phpCAS::setNoCasServerValidation(); // accept self-signed certificates (local CAS only)
\phpCAS::setCasServerCACert(__DIR__ . '/geant-ca.crt');

// Auth
\phpCAS::forceAuthentication();

// Debug user
echo "<pre>";
print_r(\phpCAS::getUser());
print_r(\phpCAS::getAttributes());
echo "</pre>";
