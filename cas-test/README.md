# Test CAS en PHP

Ce projet montre un exemple simple d'utilisation du serveur CAS `cas.u-bordeaux.fr` pour un service web `https://promo-st.emi.u-bordeaux.fr`.


**Nota Bene** : Pour qu'un service web puisse utiliser un serveur CAS, il faut que ce service soit enregistré dans le serveur CAS. Dans notre cas, le service `https://promo-st.emi.u-bordeaux.fr` est déjà enregistré dans le serveur CAS `cas.u-bordeaux.fr`.

## Installation

**Prérequis**  : Apache2, PHP 8, Composer 2.8, phpCAS 1.6.

```bash
$ sudo apt update
$ sudo apt install apache2 php composer
```


Installation des dépendances PHP à partir de `composer.json` dans `vendor/` :

```bash
$ cd cas-test
$ composer install
```

Verification des versions installées :

```bash
$ php -v
  PHP 8.4.11 (cli) (built: Aug  3 2025 07:32:21) (NTS)
$ composer --version
  Composer version 2.8.8 2025-04-04 16:56:46
$ composer show apereo/phpcas
  (...)
  versions : * 1.6.1

```

## Test Université de Bordeaux (CREMI)

Pour réaliser ce test, il faut être membre de l'Université de Bordeaux, et avoir un compte (*idnum*) sur le serveur `cas.u-bordeaux.fr`.

Le serveur de test est accessible à l'adresse : <https://promo-st.emi.u-bordeaux.fr/cas-test/test-ubx.php>. Mais il faut connaître son identifiant et mot de passe UBx pour aller plus loin...

Le code source de ce test est dans le fichier [test-ubx.php](test-ubx.php).

## Test Docker

Pour lancer le serveur CAS *Docker* sur <http://localhost:9000/cas>, il suffit de lancer le script suivant, qui se trouve dans le sous-répertoire `cas-server/` : 

```bash
$ cd cas-server
$ ./start-cas-server.sh
```

Pour lancer le service web sur `localhost:8000`, il suffit de lancer la commande suivante dans le répertoire `cas-test/` :

```bash
$ cd cas-test
$ php -S localhost:8000
```

Le code source de ce test est dans le fichier [test-docker.php](test-docker.php) et utilise le serveur CAS *Docker* <http://localhost:9000/cas>.

Pour réaliser ce test, il faut consulter <http://localhost:8000/test-docker.php> avec votre navigateur web (login/password : `toto / toto`).

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

## Logger

Les logs Apache2 / PHP sont écrits dans `/var/log/apache2/error.log`.

```php
error_log("test logger php!");
```

Dans notre cas, `phpCAS` utilise le logger *Monolog*, qu'il faut rajouter comme dépendance dans `composer.json` :

```bash
$ composer require monolog/monolog
$ composer install
```

On peut ensuite utiliser ce *logger* directement comme cela : 

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('phpCAS');
$logger->pushHandler(new StreamHandler('/var/log/phpcas/cas.log', Logger::DEBUG));
$logger->debug("test logger debug!");
$logger->info("test loger info!");
```

Enfin, on peut passer ce logger à phpCAS pour qu'il l'utilise lui aussi.

```php
\phpCAS::setLogger($logger);
\phpCAS::log("test logger phpCAS!");
```

Pour des raisons obscures, le logger *Monolog* échoue à créer un fichier dans `/tmp/` avec l'utilisateur Apache (www-data). Le plus simple pour contourner ce problème est de créer un répertoire`/var/log/phpcas/` avec des droits appropriés.

```bash
sudo mkdir /var/log/phpcas
sudo chown www-data:www-data /var/log/phpcas
sudo chmod 755 /var/log/phpcas
```

Voir le test du logger dans [test-log.php](test-log.php).

---
