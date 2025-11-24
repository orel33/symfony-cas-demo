# PHP Test


## Lancer en HTTP (local)

```bash
$ php -S localhost:8000
```

→ <http://localhost:8000>

## Lancer en HTTPS (local)

Il faut un certificat (auto-signé par exemple) : 

```bash
$ mkcert localhost
```



```bash
$ caddy file-server --cert-file localhost.pem --key-file localhost-key.pem --listen :8443
```

→ https://localhost:8443
