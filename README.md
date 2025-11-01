# Tutoriel CAS avec Symfony

Documentation Symfony : <https://symfony.com/doc>

## Prérequis

* PHP ≥ 8.1
* Composer
* Symfony CLI (facultatif mais pratique)
* Serveur CAS disponible (réel ou de test, ex. un CAS de dev universitaire)

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

## Demo Cas

```bash
$ composer create-project symfony/skeleton cas-demo
$ cd cas-demo
$ composer require symfony/web-profiler-bundle symfony/twig-bundle symfony/security-bundle
$ ...
```

### Création de la page Hello World

```bash
$ composer require symfony/maker-bundle --dev
$ php bin/console make:controller HelloController
```

* On ajoute [HelloController.php](cas-demo/src/Controller/HelloController.php) dans `src/Controller` pour afficher une simple page "Hello World!".
* On lance le serveur avec la commande `symfony serve`, qui devient accessible sur <http://localhost:8000/hello>

### Installation d'un client CAS

```bash
# $ composer require jasig/phpcas # deprecated!
# $ composer remove jasig/phpcas
$ composer require apereo/phpcas
```
Version de phpCAS :

```bash
composer show | grep -i phpcas
apereo/phpcas                      1.6.1 
```

* Web : <https://github.com/apereo/phpCAS>
* Documentation : <https://apereo.github.io/phpCAS/api/>


Il faut ensuite ajouter la page [CasAuthenticator.php](cas-demo/src/Security/CasAuthenticator.php) dans `src/Security`

On édite ensuite le fichier [security.yaml](cas-demo/config/packages/security.yaml) pour ajouter la section suivante : 

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

* *providers* — comment Symfony récupère les utilisateurs (leurs identités, rôles, etc.)
* *firewalls* — quelles zones du site nécessitent une authentification et comment
* *access_control* — quelles routes sont protégées et par quels rôles

Ici, on utilise le provider *memory*, c’est-à-dire des utilisateurs en mémoire, sans base de données. Dans une appli classique, tu aurais ici un entity provider lié à une table User en base.

Ce firewall (nommé *dev*, mais il pourrait être *public_assets*) désactive la sécurité pour certaines URL techniques de Symfony : `/css, /js, /images,` ... On ne veut pas que le CAS bloque l'accès à ces ressources.

Le firewall *main* protège toutes les autres routes du site. C’est le plus important. *custom_authenticators* indique quelle classe gère l’authentification (ici CasAuthenticator). Symfony appellera ses méthodes `supports(), authenticate(),` etc. *logout* définit une route `/logout` qui permet de se déconnecter (gérée automatiquement par Symfony),  avec *target* la page vers laquelle l’utilisateur est redirigé après déconnexion.

### Notes de sécurité

En dev, `\phpCAS::setNoCasServerValidation()` désactive la vérification SSL.
En prod, remplace par : `\phpCAS::setCasServerCACert('/path/to/cachain.pem');`

Tu peux stocker les infos du serveur CAS dans .env :

```
CAS_HOST=cas.example.com
CAS_PORT=443
CAS_CONTEXT=/cas
```

Par défaut, Symfony CLI lance le serveur en mode *dev* :

```bash 
$ symfony serve --no-tls  # APP_ENV=dev 
$ symfony serve -vvv --no-tls
```

=> regarder le fichier `.env`

On peut forcer le mode *prod* : 

```bash 
$ APP_ENV=prod symfony serve
```

### CAS de Test

```bash
$ docker run --rm -e SERVER_SSL_ENABLED=false -e SERVER_PORT=9000 -p 9000:9000 --name cas-server apereo/cas:6.4.0
```

* URL d'accès : <http://localhost:9000/cas/login>
* Identifiants de test : casuser / Mellon

Pour se connecter en interactif au serveur CAS :

```bash
$ docker exec -it cas-server /bin/sh
```

Dans le Docker CAS, on peut ajouter dans le répertoire `/etc/cas/config/` des fichiers pour modifier la config par défaut du serveur CAS, le plus simple avec un montage de volume.

En pratique, on peut ajouter dans ce répertoire un fichier `application.properties`

```json
cas.authn.accept.any.service=true  // failure?
cas.authn.accept.users=casuser::Mellon,toto::toto,tutu::tutu
```

Doc : <https://apereo.github.io/cas/7.2.x/authentication/Configuring-Authentication-Components.html>

afin d'autoriser tous les services, et ajouter des *users*.

```bash 
$ docker run --rm -e SERVER_SSL_ENABLED=false -e SERVER_PORT=9000 -p 9000:9000 -v $(pwd)/cas/config:/etc/cas/config --name cas-server apereo/cas:6.4.0
```

```bash
$ curl -u admin:admin http://localhost:9000/cas/services
```

---

On modifie les lignes suivantes...

```php
$redirecturl="..."
\phpCAS::client(CAS_VERSION_2_0, 'localhost', 9000, '/cas', redirecturl);            # docker
\phpCAS::setNoCasServerValidation(); // OK pour tests uniquement
```

Test de la page `hello` avec auth CAS : <http://localhost:8000/hello> => OK

Test direct d'authentification d'un service CAS <http://localhost:8000/hello> : 
=>  <http://localhost:9000/cas/login?service=http%3A%2F%2Flocalhost%3A8000%2Fhello>

Erreur “Application Not Authorized to Use CAS”.

En fait, il faut enregistrer dans le Docker CAS le service `http://localhost:8000` via un fichier JSON....



 `services/myservice.json` avec le service (comme je t’ai montré avant).

CAS charge automatiquement tous les services présents dans le dossier /etc/cas/config/services.

### Certificats

```bash
# openssl rsa -in cas.key -out cas.key
openssl genrsa -out cas.key 2048
openssl rsa -in cas.key -check
openssl req -x509 -nodes -days 365 -key cas.key -out cas.crt -subj "/CN=localhost"
# or
openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout cas.key -out cas.crt -subj "/CN=localhost"
```

Le serveur CAS utilise des certificats au format P12

```bash
openssl pkcs12 -export -in cas.crt -inkey cas.key -out cas-keystore.p12 -name cas -passout pass:secret
```

openssl pkcs12 -info -in cas-keystore.p12 -nodes
keytool -list -keystore cas-keystore.p12 

### Test du CAS 7.3


=> http://localhost:9000/cas/actuator/health
curl -u admin:admin http://localhost:9000/cas/actuator/services

=> http://localhost:9000/cas/actuator/health
curl -u admin:admin http://localhost:9000/cas/actuator/health

=> http://localhost:9000/cas/login?service=http://localhost:8000/hello

=> http://localhost:9000/cas/login?service=http%3A%2F%2Flocalhost%3A8000%2Fhello
=> http://localhost:9000/cas/serviceValidate?service=http%3A%2F%2Flocalhost%3A8000%2Fhello&ticket=ST-1-1wWiRz8z7-HiTUtgG2TXex8I5ZY-753de86c6a15

$ symfony serve -vvv --no-tls



---
