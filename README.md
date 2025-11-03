# Tutoriel CAS avec Symfony

*Avertissement : Il reste des **TODO** dans ce tutoriel, notamment pour utiliser la démo en HTTPS côté serveur Web (Symfony) et côté serveur CAS.*

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

## Mise en place d'un serveur CAS avec Docker

Lancons le serveur CAS en local avec le Docker fourni par *Apereo*.

```bash
$ docker run --rm -e SERVER_SSL_ENABLED=false -e SERVER_PORT=9000 -p 9000:9000 --name cas-server apereo/cas:7.3.0
```

* URL d'accès : <http://localhost:9000/cas/login>
* Identifiants par défaut de test : `casuser` (password: `Mellon`)

Pour se connecter en interactif au serveur CAS :

```bash
$ docker exec -it cas-server /bin/bash
```

### Configuration du serveur CAS

Dans le Docker CAS, on peut ajouter dans le répertoire `/etc/cas/config/` des fichiers pour modifier la config par défaut du serveur CAS, le plus simple avec un montage de volume. En pratique, on peut ajouter dans ce répertoire un fichier [application.properties](myfiles/config/application.properties) avec lis lignes suivantes.

```json
# --- Config de Base
server.port=9000
cas.server.prefix=${cas.server.name}/cas
cas.server.name=http://localhost:9000
server.ssl.enabled=false

# --- Authentification simple (utilisateurs internes)
cas.authn.accept.users=toto::toto,tutu::tutu,admin::admin
```

* Documentation : <https://apereo.github.io/cas/7.3.x/authentication/Configuring-Authentication-Components.html>

Avec ce fichier de config, il suffit de lancer le Docker de la manière suivante, comme dans le script [start-cas-server.sh](./start-cas-server.sh) :


```bash 
docker run --rm -it -p 9000:9000 -v $(pwd)/myfiles/config:/etc/cas/config --name cas-server apereo/cas:7.3.0
```

* Test du login : <http://localhost:9000/cas/login> (saisir toto / toto)

### Enregistrement d'un service auprès du serveur CAS

La dernière étape consiste à enregistrer dans le Docker CAS notre application web *Symfony* installé sur `http://localhost:8000`. 

Il suffir de rajouter les lignes suivantes dans le fichier [application.properties](myfiles/config/application.properties).

```json
# --- Enregistrement des services
cas.service-registry.core.init-from-json=true
cas.service-registry.json.location=file:/etc/cas/config/services
```

et de rajouter le fichier [symfony-demo-1.json](myfiles/config/services/symfony-demo-1.json) dans ``/etc/cas/config/services/`.

```json
{
  "@class" : "org.apereo.cas.services.CasRegisteredService",
  "serviceId" : "http://localhost:8000/.*",
  "name" : "Symfony Demo",
  "id" : 1,
  "evaluationOrder" : 1
}
```

On redémarre le serveur CAS avec Docker... 

**TODO**: Comment vérifier que le service est bien enregistré ?

### Accès CAS en HTTPS (TODO)

Il faut commencer par générer un certificat auto-signé pour `localhost`.

```bash
# openssl rsa -in cas.key -out cas.key
$ openssl genrsa -out cas.key 2048
$ openssl rsa -in cas.key -check
$ openssl req -x509 -nodes -days 365 -key cas.key -out cas.crt -subj "/CN=localhost"
```

Ou plus simplement : 

```bash
$ openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout cas.key -out cas.crt -subj "/CN=localhost"
```

Le serveur CAS utilise des certificats au format P12.

```bash
$ openssl pkcs12 -export -in cas.crt -inkey cas.key -out cas-keystore.p12 -name cas -passout pass:secret
$ openssl pkcs12 -info -in cas-keystore.p12 -nodes
$ keytool -list -keystore cas-keystore.p12 
```

Les certificats générés sont disponibles dans [myfiles/certs](myfiles/certs) et le fichier P12 doit être installé dans le répertoire `/etc/cas/ssl/` du serveur CAS.

Il faut encore modifier les lignes suivantes dans le fichier [application.properties](myfiles/config/application.properties) du serveur CAS : 


```json
# enable SSL/TLS
cas.server.name=https://localhost:9000      # à la place de http 
server.ssl.enabled=true

server.ssl.key-store-type=PKCS12
server.ssl.key-store=/etc/cas/ssl/cas-keystore.p12
server.ssl.key-store-provider=SUN
server.ssl.key-alias=cas
server.ssl.key-store-password=pouet 
```

On peut relancer le serveur Docker en lui passant l'opton `-v $(pwd)/myfiles/certs/cas-keystore.p12:/etc/cas/ssl/cas-keystore.p12`...

**BUG** : mais ça ne marche pas avec une sombre erreur de *padding* au chargement du certificat P12 !

---
