# Annexes

## Version HTTPS

### Création de la page d'accueil (TODO)

Remplaçons maintenant la page d'accueil `index.php` par défaut... 

```bash
$ php bin/console make:controller PublicController
  created: src/Controller/PublicController.php
  created: templates/public/index.html.twig
  Success! 
```

### Serveur Web en HTTPS (TODO)

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
