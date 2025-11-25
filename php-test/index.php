<?php

// Détection du protocole
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'HTTPS' : 'HTTP';

// Affichage
echo "Hello World<br>";
echo "Vous êtes sur : $protocol";