# Test CAS Client en PHP

Ce projet est un exemple simple de client CAS (Central Authentication Service) utilisant PHP avec un seul fichier de test, sans le framework *Symfony*.

## Prérequis

- PHP 7.4 ou supérieur
- Composer

## Installation

Installer les dépendances avec Composer :

```bash
composer install
```

## Lancement du serveur Web (HTTP)

Pour lancer le serveur en local sur le port 8000 :

```bash
php -S localhost:8000
```

Le fichier `test-cas.php` sera accessible à l'adresse : <http://localhost:8000/test-cas.php>

## Lancement du serveur CAS (HTTP)

Au préalable, il faudra lancer le serveur CAS sur <http://localhost:9000/cas> en utlisant le script *Docker* fourni à la racine de ce dépôt.

```bash
./start-cas-server.sh
```

## Debug

```bash
tail -f phpcas.log
```

## Configuration initiale

La commande suivante a été utilisée pour initialiser le projet :

```bash
composer init --name=test/cas-client \
    --description="Test CAS client" \
    --author="Aurelien Esnard <aurelien.esnard@u-bordeaux.fr>" \
    --type=project \
    --require="apereo/phpcas:^1.6" \
    --no-interaction
```

