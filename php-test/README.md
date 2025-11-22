# PHP Test


## Lancer en HTTP (local)

```bash
$ php -S localhost:8000
```

→ <http://localhost:8000>

## Lancer en HTTPS (local)

Il faut un certificat (auto-signé par exemple) :

```bash
php -S localhost:8443 -t . \
  -d openssl.cafile=cert.pem \
  -d openssl.local_cert=cert.pem \
  -d openssl.local_pk=key.pem
```

→ https://localhost:8443
