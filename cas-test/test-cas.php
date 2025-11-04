<?php

// Assurez-vous d'avoir installé la lib phpCAS via composer
require_once 'vendor/autoload.php';

// Configuration du debug
phpCAS::setDebug('/tmp/phpcas.log');

// Configuration de base
$cas_host = 'localhost';    // Hôte du serveur CAS
$cas_port = 9000;          // Port du serveur CAS
$cas_context = '/cas';      // Contexte du serveur CAS
$service_base_url = 'http://localhost:8000/test-cas.php'; // URL de ce script

// Initialisation du client CAS
phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context, $service_base_url);

// Désactive la validation du certificat (uniquement pour le dev)
phpCAS::setNoCasServerValidation();

// Force l'utilisation de HTTP
\phpCAS::setFixedServiceURL($service_base_url);
\phpCAS::setServerLoginURL('http://localhost:9000/cas/login?service=' . urlencode($service_base_url));
\phpCAS::setServerServiceValidateURL('http://localhost:9000/cas/serviceValidate');
\phpCAS::setServerLogoutURL('http://localhost:9000/cas/logout');
// \phpCAS::setServerLogoutURL('http://localhost:9000/cas/logout?service=' . urlencode($service_logout_url));

// Force l'authentification
\phpCAS::forceAuthentication();

// Si on arrive ici, l'utilisateur est authentifié
$user = phpCAS::getUser();

// Affiche les informations
echo "<h1>Test CAS</h1>";
echo "<p>Utilisateur authentifié : " . htmlspecialchars($user) . "</p>";
echo "<p>Attributs reçus :</p>";
echo "<pre>";
print_r(phpCAS::getAttributes());
echo "</pre>";

// Ajoute un lien de déconnexion
echo "<p><a href='?logout'>Se déconnecter</a></p>";

// Gestion de la déconnexion
if (isset($_GET['logout'])) {
    phpCAS::logout();
}

# EOF
