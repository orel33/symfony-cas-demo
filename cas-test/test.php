<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

$service = 'https://promo-st.emi.u-bordeaux.fr/'; 
\phpCAS::client(CAS_VERSION_3_0, 'cas.u-bordeaux.fr', 443, '/cas', $service);
\phpCAS::setNoCasServerValidation(); // accept self-signed certificates (local CAS only)
\phpCAS::forceAuthentication();

$user = \phpCAS::getUser();
$mail = \phpCAS::getAttribute('mail');
$email = \phpCAS::getAttribute('email');

// Affiche les informations
echo "<h1>Test CAS</h1>";
echo "<p>User : " . htmlspecialchars($user) . "</p>";
echo "<p>Mail : " . htmlspecialchars($mail) . "</p>";
echo "<p>Email : " . htmlspecialchars($email) . "</p>";
echo "<p>Attributes :</p>";
echo "<pre>";
print_r(\phpCAS::getAttributes());
echo "</pre>";

