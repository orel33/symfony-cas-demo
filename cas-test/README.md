# Test CAS en PHP

Ce projet est un exemple simple d'application web, qui est un client CAS, utilisant du PHP "pure" avec un seul fichier de test.

## Installation

Prérequis : 

- PHP 7.4 ou supérieur
- Composer

Installation des dépendances PHP à partir de `composer.json` dans `vendor/` :

```bash
cd cas-test
composer install
```

## Test Rapide

Pour lancer le serveur CAS (Docker) sur <http://localhost:9000/cas>, il suffit de lancer le script suivant, qui se trouve dans le sous-répertoire `cas-server/` : 

```bash
$ cd cas-server
$ ./start-cas-server.sh
```

Pour lancer le serveur en local sur le port 8000 :

```bash
$ cd cas-test
$ php -S localhost:8000
```

Le fichier `test-cas.php` sera accessible à l'adresse : <http://localhost:8000/test-cas.php>

Debug :

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
