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

use App\Entity\User;


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
        if (session_status() === PHP_SESSION_NONE) { // useful ?
            session_start();
        }

        error_log('[CAS] Début de l’authentification CAS');

        // Initialisation phpCAS
        \phpCAS::setDebug('/tmp/phpcas.log');
        $redirect_url = 'http://localhost:8000/hello'; # URL de retour après authentification
        \phpCAS::client(CAS_VERSION_2_0, 'localhost', 9000, '/cas', $redirect_url);
        \phpCAS::setNoCasServerValidation(); // accepte les certificats auto-signés (test en local uniquement)

        // Forcer manuellement l'URL de service en HTTP (utile en dev local)
        \phpCAS::setFixedServiceURL($redirect_url);
        \phpCAS::setServerLoginURL('http://localhost:9000/cas/login?service=' . urlencode($redirect_url));
        \phpCAS::setServerServiceValidateURL('http://localhost:9000/cas/serviceValidate');
        \phpCAS::setServerLogoutURL('http://localhost:9000/cas/logout');
        // $logout_url = 'http://localhost:8000/'; // URL de redirection après déconnexion
        // \phpCAS::setServerLogoutURL('http://localhost:9000/cas/logout?service=' . urlencode($logout_url));

        // Vérifie si l'utilisateur est authentifié, sinon redirige vers le CAS
        \phpCAS::forceAuthentication();

        $username = \phpCAS::getUser();
        error_log('[CAS] Utilisateur authentifié : ' . $username);

        $passeport = new SelfValidatingPassport(new UserBadge($username));
         
        return $passeport;

        // Crée un utilisateur "virtuel" pour Symfony
        // return new SelfValidatingPassport(
        //     new UserBadge($username, function (string $userIdentifier) {
        //         // Création à la volée d’un utilisateur (aucune BD requise)
        //         return new User($userIdentifier, ['ROLE_USER']);
        //     })
        // );
    }

    // 

    public function onAuthenticationSuccess(Request $request, $token, string $firewallName): ?Response
    {
        error_log('[CAS] onAuthenticationSuccess appelé');
        // return new RedirectResponse($this->router->generate('app_hello'));
        // Redirige vers la page d’accueil après succès
        return null; // continue la requête normalement
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        error_log('[CAS] onAuthenticationFailure appelé');
        return new Response('Erreur CAS : ' . $exception->getMessage(), Response::HTTP_UNAUTHORIZED);
    }

}
