# Demo CAS avec Symfony

Dans cette démo, nous utilisons le framework [Symfony](https://symfony.com/doc) pour programmer une application web, capable de protéger l'accès de la pahe `/hello` avec une authentification d'utilisateur délégué à un serveur CAS.

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

On peut ensuite consulter la page web <htttp://localhost:8000/hello> qui est protégé par une authentification CAS.

## Création du projet Symfony

Voici la liste des fichiers qu'il faudra ajouter / modifier dans ce projet :

* [src/Controller/HelloController.php](src/Controller/HelloController.php)
* [src/Security/CasAuthenticator.php](src/Security/CasAuthenticator.php) 
* [config/services.yaml](config/services.yaml)
* [config/packages/security.yaml](config/packages/security.yaml)
* [.env](.env)

### Configuration initiale

Prérequis :

* PHP ≥ 8.1
* Composer 2.2
* Symfony 6 & Symfony CLI

Installation de Symfony :

```bash
$ sudo apt install composer
$ composer show symfony/framework-bundle
 version: v6.4.26
```

Installation de Symfony CLI : <https://symfony.com/download>

```bash
$ wget https://get.symfony.com/cli/installer -O - | bash
  The Symfony CLI was installed successfully!
  Use it as a local file: /home/orel/.symfony5/bin/symfony
  Then start a new shell and run 'symfony'
$ sudo cp /home/orel/.symfony5/bin/symfony /usr/local/bin/symfony
$ symfony version
  Symfony CLI version 5.15.1 (c) 2021-2025 Fabien Potencier (2025-10-04T08:05:57Z - stable)
```

### Initialisation du projet `cas-demo`

```bash
$ composer create-project symfony/skeleton cas-demo
$ cd cas-demo
$ composer require symfony/web-profiler-bundle symfony/twig-bundle symfony/security-bundle
```

Création de la page *Hello World* :

```bash
$ composer require symfony/maker-bundle --dev
$ php bin/console make:controller HelloController
```

* Il faut ensuite remplacer le fichier [src/Controller/HelloController.php](src/Controller/HelloController.php), pour qu'il affiche une simple page *Hello World* associée à la route `/hello`.
* On lance le serveur avec la commande `symfony serve -vvv`.
* On peut alors consulter cette page sur <http://localhost:8000/hello>.

**TODO** : Faut-il aussi éditer le fichier `config/services.yaml` ?

### Configuration du CAS

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

**TODO** : On pourrait aussi stocker les infos du serveur CAS dans `.env` ou `.env.local` pour avoir un code plus joli :

```
APP_ENV=dev
CAS_HOST=cas.example.com
CAS_PORT=443
CAS_CONTEXT=/cas
```

### Configuration du CAS

On modifie les lignes suivantes dans le fichier [CasAuthenticator.php](src/Security/CasAuthenticator.php) :

```php
$redirecturl = 'http://localhost:8000'; // URL de retour après authentification
\phpCAS::client(CAS_VERSION_2_0, 'localhost', 9000, '/cas', $redirecturl); 
\phpCAS::setNoCasServerValidation(); // ne vérifie pas la CA du certificat du serveur CAS (test en local uniquement)
```

Maintenant, lorsqu'on essaie d'accéder à la page <http://localhost:8000/hello> de notre application web, Symfony délègue au serveur CAS l'authentification <http://localhost:9000/cas>. Le scénario est le suivant : 

1. Consultation de la page : <http://localhost:8000/hello> 
2. Redirection vers le serveur CAS : <http://localhost:9000/cas/login?service=http%3A%2F%2Flocalhost%3A8000%2Fhello>
3. Saisie des identifiants auprès du serveur CAS (`toto:toto`)
4. Si l'authentification est réussie (et que le service `localhost` est reconnu), alors le serveur CAS nous redirige sur l'URL de retour <http://localhost:8000/hello>. 
5. Si le service web `localhost` n'est pas enregistré auprès du CAS, on obtient l'erreur *Application Not Authorized to Use CAS*.

Pour simplifier la configuration du code, nous utilisons des variables d'environnement définis dans le fichier [.env](.env).

## Version HTTPS

**BUG** : Par défaut, Symfony se redirige vers le serveur CAS en HTTPS. Du coup, comme cette version est en HTTP uniquement, il faut forcer manuellement les URLs vers HTTP.

```php
\phpCAS::setFixedServiceURL($redirect_url);
\phpCAS::setServerLoginURL($cas_url . '/login?service=' . urlencode($redirect_url));
\phpCAS::setServerServiceValidateURL($cas_url . '/serviceValidate');
\phpCAS::setServerLogoutURL($cas_url . '/logout');
```

**TODO** : En pratique, il faudrait corriger le code de cette démo pour utiliser nativement HTTPS avec un certificat autosigné. A suivre...

---

