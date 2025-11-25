# Apache Demo

## Installation de Apache2

```bash
$ sudo apt install apache2
$ sudo apt install libapache2-mod-php       # module PHP
$ sudo apt install 
```

Pour *purger* une ancienne installation, commencez par faire :

```bash
$ sudo systemctl stop apache2
$ sudo rm -rf /etc/apache2
$ sudo rm -rf /var/www
$ sudo apt-get purge apache2 apache2-* libapache2-*
$ sudo apt-get autoremove --purge
```

## Les modules Apache2

Pour vérifier les modules installés dans le serveur Apache2 : 

```bash
$ apache2ctl -M                         # modules chargés actuellement
$ ls /etc/apache2/mods-available/       # modules disponibles
$ ls /etc/apache2/mods-enabled/         # modules activés       
```

Faire `a2dismod / a2enmod <module>` pour désactiver / activer un module...

Par exemple, pour activer le module *rewrite* (installé par défaut avec Apache2), il faut faire : 

```bash
$ sudo a2enmod rewrite
$ sudo systemctl restart apache2
```

## Ajout d'une simple page web 

Page Web HTML static : 

```bash
$ cd /var/www/html
$ mv index.html index.html.bak
$ echo "Hello World!" > index.html
```

Consulter le site web : <http://localhost>

Pour tester PHP, on peu juste rajouter : 

```bash
$ cd /var/www/html
$ echo "<?php phpinfo() ?>" > info.php
```

Consulter le site web : <http://localhost/info.php>

Voici un exemple simple [default.conf](config/default.conf) de configuration à placer dans `/etc/apache2/sites-available/`.

```
<VirtualHost *:80>
    # ServerName monsite.localhost
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html/

    <Directory /var/www/html/>
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Require all granted
    </Directory>

	LogLevel debug info warn
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

En résumé :

* *ServerName* : nom du Virtual Host
* *ServerAdmin* : adresse mail de l'administrateur
* *DocumentRoot* : répertoire racine du site web
* `<Directory>` : options d'accès au répertoire racine du site web
* *Indexes*, *FollowSymLinks*, *MultiViews* : indexation des répertoires, suivi des liens symboliques, négociation de contenu (lang)
* *AllowOverride All* : permet d'utiliser les fichiers `.htaccess` pour surcharger la configuration Apache2 au niveau du répertoire
* *Require all granted* : permet d'autoriser l'accès à tout le monde
* *LogLevel*, *ErrorLog*, *CustomLog* : configuration des logs

Pour activer ce Virtual Host, il faut faire : 

```bash
$ cp config/default.conf /etc/apache2/sites-available/
$ sudo a2ensite default.conf                # pour activer le nouveau site
$ sudo service apache2 restart              # pour redémarrer Apache2
$ sudo systemctl status apache2.service     # pour vérifier le statut d'Apache2 
```

Si besoin de désactiver un ancien site, par exemple 000-default.conf :

```bash
$ ls /etc/apache2/sites-enabled/   # pour lister les sites activés
$ sudo a2dissite 000-default.conf  # pour désactiver le site
```

Au final, vous pouvez consulter le sous-répertoire : <http://localhost/demo/> qui sera lister grâce à l'option `Indexes` activée dans la config.


## Accès en HTTPS 


```bash
$ sudo a2enmod ssl
$ sudo systemctl restart apache2
```

## Protection d'une page avec le CAS

Il s'agit d'une petite démode site web *static* qui montre comment utiliser *Apache2* directement pour protéger l'accès à un sous-répertoire `secret/` via une authentification CAS.

```bash
$ sudo apt install libapache2-mod-auth-cas
$ sudo a2enmod auth_cas
$ sudo systemctl restart apache2
```

## Annexes

Réinitialisation de Apache2, avec les fichiers de config `/etc/apache2/`...

Lister les modules *Apache2* installés (`apache2ctl -M`) et les packages Debian associés...




---
