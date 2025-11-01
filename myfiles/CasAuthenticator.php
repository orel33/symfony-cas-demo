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


// note Only one of the phpCAS::client() and phpCAS::proxy functions should be     
// called, only once, and before all other methods (except phpCAS::getVersion()     
//  and phpCAS::setDebug()).     
//  public static function client($server_version, $server_hostname, $server_port, $server_uri, $service_base_url,        
//                                $changeSessionID = true, \SessionHandlerInterface $sessionHandler = null) 

    public function authenticate(Request $request): SelfValidatingPassport
    {
        if (session_status() === PHP_SESSION_NONE) { // useful ?
            session_start();
        }

        // ⚡ Initialisation phpCAS
        \phpCAS::setDebug('/tmp/phpcas.log');
        $redirecturl = 'http://localhost:8000'; # URL de retour après authentification
        \phpCAS::client(CAS_VERSION_2_0, 'localhost', 9000, '/cas', $redirecturl); # FIXME: lookup for https://localhost:9000/cas
        \phpCAS::setNoCasServerValidation(); // accepte les certificats auto-signés (test en local uniquement)

        // 🔒 Force la connexion CAS
        // \phpCAS::setFixedServiceURL('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        // \phpCAS::setFixedServiceURL('http://localhost:8000/hello'); // forcer le retour sur cette URL en HTTP!
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
