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
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use phpCAS;

class CasAuthenticator extends AbstractAuthenticator
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function supports(Request $request): ?bool
    {
        return true; // toujours activer l'authentification CAS
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        error_log('[CAS] Début de l’authentification CAS');

        if (session_status() === PHP_SESSION_NONE)
            session_start();


        \phpCAS::setDebug('/tmp/phpcas.log');

        // Accès direct aux variables d'environnement
        $cas_hostname = $_ENV['CAS_SERVER_HOSTNAME'] ?? 'localhost';
        $cas_port = $_ENV['CAS_SERVER_PORT'] ?? '9000';
        $cas_uri = $_ENV['CAS_SERVER_URI'] ?? '/cas';

        // Construire l'URL du serveur CAS
        // $cas_url = 'http://localhost:9000/cas';
        $cas_url = "http://$cas_hostname:$cas_port$cas_uri";
        error_log('CAS URL: ' . $cas_url);

        $redirect_url = 'http://localhost:8000/hello'; # URL de retour après authentification
        // \phpCAS::client(CAS_VERSION_2_0, 'localhost', 9000, '/cas', $redirect_url);
        \phpCAS::client(CAS_VERSION_2_0, $cas_hostname, (int) $cas_port, $cas_uri, $redirect_url);

        // Désactive la validation du serveur CAS (pour les tests en local)
        \phpCAS::setNoCasServerValidation(); // accepte les certificats auto-signés (test en local uniquement)

        // Forcer manuellement l'URL de service en HTTP (utile en dev local)
        \phpCAS::setFixedServiceURL($redirect_url);
        \phpCAS::setServerLoginURL($cas_url . '/login?service=' . urlencode($redirect_url));
        \phpCAS::setServerServiceValidateURL($cas_url . '/serviceValidate');
        \phpCAS::setServerLogoutURL($cas_url . '/logout');

        // Vérifie si l'utilisateur est authentifié, sinon redirige vers le CAS
        \phpCAS::forceAuthentication();

        $username = \phpCAS::getUser();
        error_log('[CAS] Utilisateur authentifié : ' . $username);

        $passeport = new SelfValidatingPassport(new UserBadge($username));
        return $passeport;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        error_log('[CAS] onAuthenticationSuccess appelé');
        // return new Response('Bienvenue sur la page protégée', Response::HTTP_OK);
        // return new RedirectResponse($this->router->generate('app_hello'));
        return null; // continue la requête normalement
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        error_log('[CAS] onAuthenticationFailure appelé');
        return new Response('Erreur CAS : ' . $exception->getMessage(), Response::HTTP_UNAUTHORIZED);
    }

}
