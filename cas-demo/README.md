# Tutoriel CAS avec Symfony

*Avertissement : Il reste des **TODO** dans ce tutoriel, notamment pour utiliser la démo en HTTPS côté serveur Web (Symfony) et côté serveur CAS.*


## Demo Rapide

Pour lancer le serveur CAS (Docker) sur <http://localhost:9000/cas>, il suffit de lancer le script suivant : 

```bash
$ ./start-cas-server.sh
```

L'application web qui teste le CAS se trouve dans le sous-répertoire `cas-demo`. On commence par installer les dépendances.

```bash
$ cd cas-demo
$ composer install
```

Pour lancer le serveur web en mode *dev* et sans HTTPS :

```bash
$ symfony serve -vvv --no-tls
```

On peut ensuite consulter la page web <htttp://localhost:8000/hello> qui est protégé par une authentification CAS.

## Prérequis

* PHP ≥ 8.1
* Composer
* Symfony 6 & Symfony CLI
* Serveur CAS de test

```bash
$ sudo apt install composer
$ composer show symfony/framework-bundle
 version: v6.4.26
```

Download the Symfony CLI at <https://symfony.com/download> to install a development web server.

```bash
$ wget https://get.symfony.com/cli/installer -O - | bash
  The Symfony CLI was installed successfully!
  Use it as a local file: /home/orel/.symfony5/bin/symfony
  Then start a new shell and run 'symfony'
$ sudo cp /home/orel/.symfony5/bin/symfony /usr/local/bin/symfony
$ symfony version
  Symfony CLI version 5.15.1 (c) 2021-2025 Fabien Potencier (2025-10-04T08:05:57Z - stable)
```

* Documentation Symfony : <https://symfony.com/doc>

## Demo Cas avec Symfony

```bash
$ composer create-project symfony/skeleton cas-demo
$ cd cas-demo
$ composer require symfony/web-profiler-bundle symfony/twig-bundle symfony/security-bundle
```

### Création de la page Hello World

```bash
$ composer require symfony/maker-bundle --dev
$ php bin/console make:controller HelloController
```

* On ajoute [HelloController.php](myfiles/HelloController.php) dans `src/Controller` pour afficher une simple page "Hello World!".
* On lance le serveur avec la commande `symfony serve`, qui devient accessible sur <http://localhost:8000/hello>


### Création de la page d'accueil (TODO)

Remplaçons maintenant la page d'accueil `index.php` par défaut... 

```bash
$ php bin/console make:controller PublicController
  created: src/Controller/PublicController.php
  created: templates/public/index.html.twig
  Success! 
```

### Installation d'un client CAS

On veut maintenant protéger l'accès à la page *hello* via le CAS. L'application *Symfony* joue donc le rôle d'un client CAS, ce qui suppose que l'on dispose d'un serveur CAS en place.

```bash
$ composer require apereo/phpcas
# Version de phpCAS :
$ composer show | grep -i phpcas
  apereo/phpcas => 1.6.1 
```

* Web : <https://github.com/apereo/phpCAS>
* Documentation : <https://apereo.github.io/phpCAS/api/>

Il faut ensuite ajouter la page [CasAuthenticator.php](myfiles/CasAuthenticator.php) dans `src/Security/`. 

On édite ensuite le fichier [security.yaml](myfiles/security.yaml) dans `config/packages/` pour ajouter la section suivante : 

```yaml
security:
  providers:
    app_user_provider:
      memory: ~  # Pas de base de données, juste utilisateur CAS

  firewalls:
    dev:
      pattern: ^/(_(profiler|wdt)|css|images|js)/
      security: false

    main:
      custom_authenticators:
        - App\Security\CasAuthenticator
      logout:
        path: /logout
        target: /

  access_control:
    - { path: ^/hello, roles: ROLE_USER }
```

Le fichier `security.yaml` est divisé en trois grandes sections :

* *providers* : comment Symfony récupère les utilisateurs (leurs identités, rôles, etc.) ;
* *firewalls* :  quelles zones du site nécessitent une authentification et comment ;
* *access_control* : quelles routes sont protégées et par quels rôles.

Ici, on utilise le provider *memory*, c’est-à-dire des utilisateurs en mémoire, sans base de données. Dans une appli classique, on utiliserait plutôt un *entity* provider lié à une table User en base.

Le firewall (nommé *dev*, mais il pourrait autrement) désactive la sécurité pour certaines URL techniques de Symfony : `/css, /js, /images,` ... On ne veut pas que le CAS bloque l'accès à ces ressources.

Le firewall *main* protège toutes les autres routes du site. C’est le plus important. *custom_authenticators* indique quelle classe gère l’authentification (ici CasAuthenticator). Symfony appellera ses méthodes `supports(), authenticate(),` etc. *logout* définit une route `/logout` qui permet de se déconnecter (gérée automatiquement par Symfony), avec *target* la page vers laquelle l’utilisateur est redirigé après déconnexion.

**Nota Bene** : En dev, `\phpCAS::setNoCasServerValidation()` désactive la vérification SSL. Utile surtout si on utilise un certificat auto-signé dont la CA n'est pas reconnu par le système. En prod, on peut le remplacer par : `\phpCAS::setCasServerCACert('/path/to/ca.pem');`

On pourrait aussi stocker les infos du serveur CAS dans `.env` ou `.env.local` pour avoir un code plus joli :

```
APP_ENV=dev
CAS_HOST=cas.example.com
CAS_PORT=443
CAS_CONTEXT=/cas
```

Par défaut, Symfony CLI lance le serveur en mode *dev* :

```bash 
$ symfony serve -vvv --no-tls
```

On peut forcer serveur en mode *prod* : 

```bash 
$ APP_ENV=prod symfony serve
```

### Configuration du Client CAS

On modifie les lignes suivantes dans le fichier [CasAuthenticator.php](myfiles/CasAuthenticator.php) :

```php
$redirecturl = 'http://localhost:8000'; // URL de retour après authentification
\phpCAS::client(CAS_VERSION_2_0, 'localhost', 9000, '/cas', $redirecturl); 
\phpCAS::setNoCasServerValidation(); // ne vérifie pas la CA du certificat du serveur CAS (test en local uniquement)
```

Maintenant, lorsqu'on essaie d'accéder à la page <http://localhost:8000/hello> de notre application web Symfony, celle-ci délègue au serveur CAS l'authentification <http://localhost:9000/cas>. Le scénario est le suivant : 

1. Consultation de la page : <http://localhost:8000/hello> 
2. Redirection vers le serveur CAS : <http://localhost:9000/cas/login?service=http%3A%2F%2Flocalhost%3A8000%2Fhello>
3. Saisie des identifiants auprès du serveur CAS.
4. Si l'authentification est réussie (et que le service est reconnu), alors le serveur CAS nos redirige sur l'URL de retour <http://localhost:8000/hello>.

**Bug** : Symfony se redirige *obligatoirement* vers le serveur CAS en HTTPS.

Si le service web `localhost` n'est pas enregistré auprès du CAS, on obtient l'erreur *Application Not Authorized to Use CAS*.

## Serveur Web en HTTPS (TODO)

Installation d'une autorité de certification locale dans `/etc/ssl/certs/` et d'un certificat auto-signé dans `~/.symfony/certs/`.

```bash
$ symfony server:ca:install
```

Variante avec la commande `mkcert`

```bash
$ sudo apt install mkcert libnss3-tools
$ mkcert -install               # install CA in ~/.local/share/mkcert/ for certificates to be trusted automatically
$ mkdir -p ~/.symfony/certs
$ cd ~/.symfony/certs
$ mkcert localhost 127.0.0.1 ::1
$ openssl x509 -in localhost.pem -noout -text 
```

**Nota Bene** : Dans ce certificat, le CN n'est pas localhost, mais les SAN (Subject Alternative Name) sont corrects et bien pris en compte par les navigateurs modernes (qui ignorent le CN).

```bash
$ symfony serve -vvv
$ symfony server:status
```

---
