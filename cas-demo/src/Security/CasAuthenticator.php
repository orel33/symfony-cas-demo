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

// use phpCAS;

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

        // cas server configuration
        $cas_hostname = $_ENV['CAS_SERVER_HOSTNAME'] ?? 'localhost';
        $cas_port = $_ENV['CAS_SERVER_PORT'] ?? '9000';
        $cas_uri = $_ENV['CAS_SERVER_URI'] ?? '/cas';
        $cas_url = "http://$cas_hostname:$cas_port$cas_uri/"; # don't forget trailing slash!

        // service configuration
        $service_url = 'http://localhost:8000/';
        
        // initialize phpCAS
        \phpCAS::client(CAS_VERSION_2_0, $cas_hostname, (int) $cas_port, $cas_uri, $service_url);     
        $client = \phpCAS::getCasClient();
        $client->setBaseURL($cas_url);  // for HTTP CAS server (local CAS only)
        \phpCAS::setNoCasServerValidation(); // accept self-signed certificates (local CAS only)
        
        // force CAS authentication
        \phpCAS::forceAuthentication();

        $username = \phpCAS::getUser();
        error_log('[CAS] Utilisateur authentifié : ' . $username);

        $passeport = new SelfValidatingPassport(new UserBadge($username));
        return $passeport;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        error_log('[CAS] call onAuthenticationSuccess()');
        return null; // continue
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        error_log('[CAS] call onAuthenticationFailure()');
        // return new RedirectResponse('/');
        return new Response('Erreur CAS : ' . $exception->getMessage(), Response::HTTP_UNAUTHORIZED);

    }

}
