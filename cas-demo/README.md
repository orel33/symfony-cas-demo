# Démo CAS avec Symfony

**Disclaimer** : Rédigé automatiquement par ChatGPT, relu et corrigé par <aurelien.esnard@u-bordeaux.fr>.

Cette application est une **démo d'authentification CAS** (Central Authentication Service) intégrée dans un projet Symfony 6. Elle illustre comment gérer l'authentification via CAS, les routes publiques et privées, et le logout.

## 📦 Fonctionnalités

- **Page d'accueil (`/`)** : publique, accessible sans authentification
- **Page publique (`/public`)** : accessible sans login
- **Page privée (`/private`)** : nécessite l'authentification CAS
- **Login CAS (`/login`)** : déclenche l'authentification CAS
- **Logout Symfony (`/logout`)** : déconnecte l'utilisateur localement
- **Logout CAS (`/cas-logout`)** : déconnecte l'utilisateur du serveur CAS et revient sur *home*
- **Affichage des infos utilisateur** : nom et attributs CAS (ex : email) dans les pages privées

## ⚙️ Architecture

### 1. Firewall et sécurité (`config/packages/security.yaml`)

- **Provider CAS** : `App\Security\CasUserProvider`
- **Authenticator custom** : `App\Security\CasAuthenticator`
- **Entry point CAS** : `App\Security\CasEntryPoint`
- **Access control** :

```yaml
access_control:
  - { path: ^/public, roles: PUBLIC_ACCESS }
  - { path: ^/private, roles: ROLE_USER }
  - { path: ^/login, roles: ROLE_USER }
  - { path: ^/, roles: PUBLIC_ACCESS }
```

- Les pages privées (`ROLE_USER`) déclenchent le login CAS si l'utilisateur n'est pas authentifié.

### 2. CasAuthenticator (`src/Security/CasAuthenticator.php`)

- Implémente un **authenticator Symfony 6**.
- Méthodes principales :
  - `supports()` : active CAS pour toutes les routes protégées
  - `authenticate()` : force l'authentification CAS avec `phpCAS::forceAuthentication()`
  - `onAuthenticationSuccess()` : redirige vers la page initialement demandée


### 3. CasEntryPoint (`src/Security/CasEntryPoint.php`)

Implémente la méthode `start()` de `AuthenticationEntryPointInterface` :
  - Redirige les utilisateurs non authentifiés vers `/login` pour déclencher CAS
  - Nécessaire pour que Symfony sache **où commencer l'authentification** sur les pages protégées

### 4. Logout

- **Symfony logout** : `/logout` (invalide la session locale)
- **CAS logout** : `/cas-logout` (déconnecte du serveur CAS et revient sur `/`)

---

### 5. Routes principales

| Route           | Accès           | Description |
|-----------------|----------------|------------|
| `/`             | Public         | Home page avec bouton login/logout et affichage info utilisateur |
| `/public`       | Public         | Page publique |
| `/private`      | ROLE_USER      | Page privée, nécessite CAS |
| `/login`        | ROLE_USER      | Déclenche CAS |
| `/logout`       | ROLE_USER      | Logout Symfony |
| `/cas-logout`   | ROLE_USER      | Logout CAS et retour home |


## 📝 Instructions

On suppose que *symfony* 6 est correctement installé.

1. Installer les dépendances :

```bash
composer install
```

2. Configurer les variables d'environnement CAS (`.env.local`) :

```
CAS_SERVER_HOSTNAME="cas.u-bordeaux.fr"
CAS_SERVER_PORT="443"
CAS_SERVER_URI="/cas"
CAS_SERVICE_URL="https://promo-st.emi.u-bordeaux.fr/"
```

3. Lancer le serveur Symfony :

```bash
symfony server:start
```

4. Tester la démo :

Aller sur *home* `/` et choisir les liens vers les pages suivantes :

- Public → `/public` ou `/` → accessible sans login
- Login → `/login` → déclenche CAS → redirection sur `/`
- Private → `/private` → déclenche CAS → redirection sur `/`
- Logout → `/cas-logout` (ou `/logout`)

### 💡 Notes

- Les informations CAS (login, attributs comme email) sont stockées dans le **CasUser** et accessibles via `$this->getUser()` dans les controllers
- Le redirect CAS se fait systématiquement vers `/` au lieu de la page demandée... FIXME: il faut utiliser **TargetPath** 
- La démo fonctionne avec **phpCAS** et un serveur CAS configuré correctement

