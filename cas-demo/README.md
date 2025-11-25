# Demo CAS avec Symfony

Dans cette démo, nous utilisons le framework [Symfony](https://symfony.com/) (version 6.4) pour programmer une application web, qui utilise une authentification d'utilisateur auprès d'un serveur CAS.

* Documentation de Symfony 6.4 : <https://symfony.com/doc/6.4/index.html>

## Démo Rapide

Pour lancer le serveur CAS (Docker) sur <http://localhost:9000/cas>, il suffit de lancer le script suivant, qui se trouve dans le sous-répertoire `cas-server/` : 

```bash
$ ./start-cas-server.sh
```

L'application web qui teste le CAS se trouve dans le sous-répertoire `cas-demo`. On commence par installer les dépendances dans `vendor`.

```bash
$ composer install
```

Pour lancer le serveur web en mode *dev* (et sans HTTPS) :

```bash
$ symfony serve -vvv --no-tls
```

On peut ensuite consulter les pages web suivantes : 

* <http://localhost:8000/>, accès public (pas d'authentification CAS) ;
* <http://localhost:8000/hello>, authentification CAS requise avec le rôle *user* (`toto:toto`) ;
* <http://localhost:8000/admin>, authentification CAS requise avec le rôle *admin* (`admin:admin`).


## Création du projet Symfony

Voici la liste des fichiers qu'il faudra ajouter et/ou modifier dans ce projet :

* [src/Controller/HelloController.php](src/Controller/HelloController.php) => controller de la page `/hello` (rôle *user*)
* [src/Controller/AdminController.php](src/Controller/AdminController.php) => controller de la page `/admin`  (rôle *admin*)
* [src/Security/CasAuthenticator.php](src/Security/CasAuthenticator.php) => service d'authentification CAS
* [config/services.yaml](config/services.yaml) => configuration des différents services *autowire* & *autoconfigure*
* [config/packages/security.yaml](config/packages/security.yaml) => configuration de la sécurité...
* [.env](.env) => variables d'environnement


On initialise le projet `cas-demo` :

```bash
$ composer create-project symfony/skeleton cas-demo
$ cd cas-demo
$ composer require symfony/web-profiler-bundle symfony/twig-bundle symfony/security-bundle
```

Création de la page `/hello` avec le rôle *user* :

```bash
$ composer require symfony/maker-bundle --dev
$ php bin/console make:controller HelloController
  created: src/Controller/HelloController.php
  created: templates/hello/index.html.twig
```

* Il faut ensuite remplacer le fichier [src/Controller/HelloController.php](src/Controller/HelloController.php), pour qu'il affiche une simple page *Hello World* associée à la route `/hello`.
* On lance le serveur avec la commande `symfony serve -vvv --no-tls`.
* On peut alors consulter cette page sur <http://localhost:8000/hello>.

Faire de même pour la page `/admin` avec le rôle *admin* et `/public` avec un accès *public* non authentifié...

TODO: Ajouter la page d'accueil `/` public.


### Installation de phpCAS

On veut maintenant protéger l'accès à la page *hello* via le CAS. L'application *Symfony* joue donc le rôle d'un client CAS, ce qui suppose que l'on dispose d'un serveur CAS en place.

Ajout des dépendances :

```bash
$ composer require apereo/phpcas
# Version de phpCAS :
$ composer show | grep -i phpcas
  apereo/phpcas => 1.6.1 
```

* Web : <https://github.com/apereo/phpCAS>
* Documentation : <https://apereo.github.io/phpCAS/api/>

Il faut commencer par ajouter le fichier [src/Security/CasAuthenticator.php](src/Security/CasAuthenticator.php), qui joue le rôle dans Symfony d'un *Custom Authenticator* basé sur [phpCAS](https://github.com/apereo/phpCAS).

En mode *dev*, `\phpCAS::setNoCasServerValidation()` désactive la vérification SSL. C'est utile surtout si on utilise un certificat auto-signé dont la CA n'est pas reconnu par le système. En mode *prod*, on peut le remplacer par : `\phpCAS::setCasServerCACert('/path/to/ca.pem');`

On remplace ensuite le fichier [config/packages/security.yaml](config/packages/security.yaml) pour préciser que la route `/hello` nécessitera une authentification CAS (appel à la méthode `CasAuthenticator::delegate()`).

Le fichier `security.yaml` est divisé en trois grandes sections :

* *providers* : comment Symfony récupère les utilisateurs (leurs identités, rôles, etc.) ;
* *firewalls* :  quelles zones du site nécessitent une authentification et comment ;
* *access_control* : quelles routes sont protégées et par quels rôles.

Ici, on utilise le provider *memory*, c’est-à-dire des utilisateurs en mémoire (sans base de données). Dans une application classique, on utiliserait plutôt un *entity* provider lié à une table User en base. Notre *provider* est nommé *app_user_provider* et n'autorise que les utilisateurs `toto` et `admin`, en précisant leur rôle.

Le firewall (nommé *dev*, mais il pourrait s'appeler autrement) désactive la sécurité pour certaines URL techniques de Symfony : `/css, /js, /images,` ... On ne veut pas que le CAS bloque l'accès à ces ressources.

Le firewall *main* protège toutes les autres routes du site. C’est le plus important pour notre configuration CAS.

* *custom_authenticators* indique quelle classe gère l’authentification (ici c'est notre classe `CasAuthenticator`). Symfony appellera ses méthodes `supports(), authenticate(),` etc.  
* *provider* pointe sur *app_user_provider*, défini précédemment.
* *logout* définit une route `/logout` qui permet de se déconnecter (gérée automatiquement par Symfony), avec *target* la page vers laquelle l’utilisateur est redirigé après déconnexion.

Par défaut, Symfony CLI lance le serveur en mode *dev* :

```bash 
$ symfony serve -vvv --no-tls
```

### Configuration du Client CAS

Voici la doc de `\phpCAS::client()` dans la version 1.6.0+.

```php
/**
 * phpCAS client initializer.
 *
 * @param string  $server_version    the version of the CAS server
 * @param string  $server_hostname   the hostname of the CAS server
 * @param int     $server_port       the port the CAS server is running on
 * @param string  $server_uri        the URI the CAS server is responding on
 * @param string  $service_base_url  the base URL (protocol, host and the optional port) of the CAS client.
 */
```

Dans le fichier [CasAuthenticator.php](src/Security/CasAuthenticator.php), on modifie la fonction `authenticate()` :

```php
$service_url = 'http://localhost:8000'; // URL de retour après authentification
\phpCAS::client(CAS_VERSION_2_0, 'localhost', 9000, '/cas', $service_url); 
\phpCAS::setNoCasServerValidation(); // ne vérifie pas la CA du certificat du serveur CAS (test en local uniquement)
```

Maintenant, lorsqu'on essaie d'accéder à la page <http://localhost:8000/hello> de notre application web, Symfony délègue au serveur CAS l'authentification <http://localhost:9000/cas>. Le scénario est le suivant : 

1. Consultation de la page : <http://localhost:8000/hello> 
2. Redirection vers le serveur CAS : <http://localhost:9000/cas/login?service=http%3A%2F%2Flocalhost%3A8000%2Fhello>
3. Saisie des identifiants auprès du serveur CAS (`toto:toto`)
4. Si l'authentification est réussie (et que le service `localhost` est reconnu), alors le serveur CAS nous redirige sur l'URL de retour <http://localhost:8000/hello>. 
5. Si le service web `localhost` n'est pas enregistré auprès du CAS, on obtient l'erreur *Application Not Authorized to Use CAS*.

Pour simplifier la configuration du code, nous utilisons des variables d'environnement définis dans le fichier [.env](.env).

```bash
CAS_SERVER_HOSTNAME=localhost
CAS_SERVER_PORT=9000
CAS_SERVER_URI=/cas
```

On peut ensuite récupérer la valeur de ses variables en PHP avec `$_ENV['CAS_SERVER_HOSTNAME']`.

**Notab Bene** : Par défaut, Symfony 1.6 se redirige vers le serveur CAS en HTTPS, même en utilisant l'option `--no-tls`. Du coup, notre serveur CAS de démo est uniquement accessible en HTTP, il faut forcer manuellement les URLs.

```php
$cas_url = 'http://localhost:9000/cas/'; # with trailing slash
# solution 1
$client = \phpCAS::getCasClient();
$client->setBaseURL($cas_url);
# solution 2
\phpCAS::setServerLoginURL($cas_url . 'login?service=' . urlencode($redirect_url));
\phpCAS::setServerServiceValidateURL($cas_url . 'serviceValidate');
\phpCAS::setServerLogoutURL($cas_url . 'logout');
```

## Version HTTPS

**TODO** : En pratique, il faudrait corriger le code de cette démo pour utiliser nativement HTTPS avec un certificat autosigné. A suivre...

---

