# Demo Symfony avec Apache

## Installation de Symfony

On suppose que Apache2 et PHP8 sont déjà installés.

On commence par installer Composer (si ce n'est pas déjà fait) :

```bash
$ sudo apt install composer
$ composer show symfony/framework-bundle
 version: v6.4.26
```

Puis on installe Symfony CLI : <https://symfony.com/download>

```bash
$ wget https://get.symfony.com/cli/installer -O - | bash
  The Symfony CLI was installed successfully!
  Use it as a local file: /home/orel/.symfony5/bin/symfony
  Then start a new shell and run 'symfony'
$ sudo cp /home/orel/.symfony5/bin/symfony /usr/local/bin/symfony
$ symfony version
  Symfony CLI version 5.15.1 (c) 2021-2025 Fabien Potencier (2025-10-04T08:05:57Z - stable)
```



## Création du projet `demo`

```bash
$ composer create-project symfony/skeleton demo
$ cd demo
$ composer require symfony/web-profiler-bundle symfony/twig-bundle symfony/security-bundle
$ composer require symfony/maker-bundle --dev
```

Création de la page `/hello` :

```bash
$ php bin/console make:controller HelloController
  created: src/Controller/HelloController.php
  created: templates/hello/index.html.twig
```

Pour lancer le serveur web en mode *dev* (et sans HTTPS) :

```bash
$ symfony check:requirements
$ symfony serve -vvv --no-tls
```

On peut alors consulter cette page sur <http://localhost:8000/hello>.


## Déploiement dans Apache2

Il ne faut pas oublier d'activer le module *rewrite*, qui est important pour que le framework Symfony puisse gérer les routes correctement.

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Activer le site `symfony` en copiant le fichier de configuration [symfony.conf](config/symfony.conf) dans Apache2 :

```bash
sudo cp config/symfony.conf /etc/apache2/sites-available/
sudo aen2site synfony
```

Nettoyage du cache Symfony :

```bash
$ cd demo
$ php bin/console cache:clear
```

Ajouter le fichier [.htaccess](config/htaccess) dans le répertoire `public/` si il n'est pas déjà fourni par le framework Symfony. 

```bash
$ cp config/htaccess demo/public/.htaccess
```

**Nota Bene** : Le fichier *htaccess* indique à Apache2 de rediriger toutes les routes de notre projet vers le *frontend* `index.php`, sauf si le fichier existe vraiment. 

Il faut ensuite copier `demo/` dans `/var/www/` et corriger les permissions :

```bash
$ sudo cp -rf demo /var/www/                        
$ sudo chown -R www-data:www-data /var/www/demo
# ou avec un lien symbolique
```

Ainsi le site sera consultable en local sur <http://localhost/>.

**Remarques** : 

* Attention aux permissions, le serveur Apache2 s'exécute comme l'utilisateur `www-data`...
* Il faut que le `<DocumentRoot>` d'Apache2 pointe vers le répertoire `public/` du projet Symfony.
* Il est nécessaire d'activer le module `rewrite` d'Apache2 pour que le framework Symfony puisse gérer les routes correctement.
* Il faut ajouter un fichier `.htaccess` dans le répertoire `public/` si il n'est pas déjà fourni par le framework Symfony.

## Mode production

Une fois que tout fonctionne correctement en mode *dev*, on peut passer en mode *production*.

Il faut commencer par positionner les variables d'environnement dans le fichier `.env` à la racine du projet :

```
APP_ENV=prod
APP_DEBUG=0
```

Nettoyage et optimisation du cache Symfony pour le mode *production* :

```bash
$ php bin/console cache:clear --env=prod
$ php bin/console cache:warmup --env=prod
```

Le *profiler* est désactivé et on tombe sur des erreurs 404 en cas de problème dans le code PHP.

## Annexes

## Déploiement dans Apache2

Il faut commencer à copier `cas-demo/*` dans `/var/www/symfony/`.

Puis il faut éditer le fichier `/etc/apache2/sites-available/000-default.conf` en ajoutant ceci à l’intérieur du `<VirtualHost>`

```
Alias /symfony /var/www/symfony/public

<Directory /var/www/symfony/public>
    AllowOverride All
    Require all granted
</Directory>
```

Ainsi le site sera consultable en local sur <http://localhost/symfony/>.


Il ne faut pas oublier d'activer le mode *rewrite*... avant de redémarrer le serveur Apache2.

```bash
$ sudo a2enmod rewrite
$ sudo systemctl restart apache2
```
