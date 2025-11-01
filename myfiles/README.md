Parfait 😎 — voici la **version finale minimaliste** de ton projet Symfony + CAS, compatible avec **Symfony 6** et **apereo/phpcas 1.6.x**, prête à fonctionner avec ton Docker CAS local.

---

## 1️⃣ `CasAuthenticator.php` (minimal)

```php
<?php
namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class CasAuthenticator extends AbstractAuthenticator
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function supports(Request $request): ?bool
    {
        return true;
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // ⚡ Initialisation phpCAS
        \phpCAS::setDebug('/tmp/phpcas.log');
        \phpCAS::client(CAS_VERSION_2_0, 'localhost', 9000, '/cas', true);
        \phpCAS::setNoCasServerValidation(); // Test local uniquement

        // 🔒 Force la connexion CAS
        \phpCAS::forceAuthentication();

        $username = \phpCAS::getUser();
        error_log('[CAS] Utilisateur authentifié : ' . $username);

        return new SelfValidatingPassport(new UserBadge($username));
    }

    public function onAuthenticationSuccess(Request $request, $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->router->generate('app_hello'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new Response('Échec de l’authentification CAS', Response::HTTP_UNAUTHORIZED);
    }
}
```

---

## 2️⃣ `HelloController.php`

```php
<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HelloController extends AbstractController
{
    #[Route('/hello', name: 'app_hello')]
    public function hello(): Response
    {
        $username = \phpCAS::getUser();
        return new Response('<h1>Hello ' . htmlspecialchars($username) . ' !</h1>');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \Exception('Cette méthode peut rester vide.');
    }
}
```

---

## 3️⃣ `security.yaml` minimaliste

```yaml
security:
  enable_authenticator_manager: true

  providers:
    app_user_provider:
      memory: ~

  firewalls:
    dev:
      pattern: ^/(_(profiler|wdt)|css|images|js)/
      security: false

    main:
      lazy: true
      provider: app_user_provider
      custom_authenticators:
        - App\Security\CasAuthenticator
      logout:
        path: /logout
        target: /

  access_control:
    - { path: ^/hello, roles: ROLE_USER }
    - { path: ^/, roles: PUBLIC_ACCESS }
```

---

## 4️⃣ Lancer ton Docker CAS local

```bash
docker run --rm -e SERVER_SSL_ENABLED=false -e SERVER_PORT=9000 -p 9000:9000 apereo/cas
```

* Test utilisateur : `casuser` / `Mellon`
* Vérifie le log `/tmp/phpcas.log` pour suivre la redirection et l’authentification.

---

## 5️⃣ Lancer Symfony

```bash
symfony serve
```

Accède ensuite à : [http://localhost:8000/hello](http://localhost:8000/hello)

* Tu seras redirigé vers le login CAS.
* Après authentification, tu reviens sur `/hello` avec :

```
Hello casuser !
```

---

💡 Astuce debug : si la page reste blanche, vérifie `/tmp/phpcas.log` et la console Symfony (`-vvv`).
Tu verras exactement ce que fait phpCAS lors de la redirection et du ticket.

---

Si tu veux, je peux te fournir **une version prête à copier-coller avec Docker Compose + Symfony**, pour que tu puisses lancer la démo en **5 minutes chrono**, sans aucune configuration supplémentaire.

Veux‑tu que je fasse ça ?
