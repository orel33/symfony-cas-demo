# CAS Server

Pour lancer le serveur CAS en local, on va utiliser l'image *Docker* officielle fournie par [Apereo](https://hub.docker.com/r/apereo/cas/tags).  Un tel serveur sur `localhost` est esssentiellement utile au test & développement d'application web (client CAS).

## Lancement du Serveur CAS

Le script [start-cas-server.sh](start-cas-server.sh) configure le serveur CAS et le démarre en local sur <http://localhost:9000/cas>.


```bash
$ ./start-cas-server.sh
```

Les fichiers de configuration du serveur CAS utilisés par ce script sont disponibles dans [config/](config) : 

* [/etc/cas/config/application.properties](config/application.properties)
* [/etc/cas/config/services/symfony-demo-1.json](config/services/symfony-demo-1.json)

En particulier, dans `application.properties`, on donne la configuration de base du serveur CAS (*port*, *prefix*, *ssl*), ainsi que les utilisateurs acceptés (`toto:toto`, `tutu:tutu`, etc). Le répertoire `/etc/cas/config/services/` contient les fichiers JSON qui décrivent les services enregistrés auprès du CAS.

Documentation : <https://apereo.github.io/cas/7.3.x/authentication/Configuring-Authentication-Components.html>

Pour tester le serveur CAS, consultez l'URL <http://localhost:9000/cas/login> avec votre navigateur et saisir `login: toto` et `password: toto`.

**TODO**: Comment vérifier que le service est bien enregistré ?

## Image Docker officiel

Pour lancer l'image Docker officielle (sans les configurations ci-dessus) :

```bash
$ docker run --rm -e SERVER_SSL_ENABLED=false -e SERVER_PORT=9000 -p 9000:9000 --name cas-server apereo/cas:7.3.0
```

On peut alors tester le serveur CAS sur <http://localhost:9000/cas/login> avec `login: casuser`et `password: Mellon`.

Pour se connecter en interactif au serveur CAS, on peut taper :

```bash
$ docker exec -it cas-server /bin/bash
```

## Version HTTPS du Serveur CAS

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

Les certificats générés sont disponibles dans [certs](certs) et le fichier P12 doit être installé dans le répertoire `/etc/cas/ssl/` du serveur CAS.

Il faut encore modifier les lignes suivantes dans le fichier `application.properties` du serveur CAS : 

```
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
