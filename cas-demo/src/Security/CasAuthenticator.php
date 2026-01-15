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
use Symfony\Component\Security\Core\User\InMemoryUser;

use App\Security\User\CasUser;
use App\Security\CasHelper;



class CasAuthenticator extends AbstractAuthenticator
{
    private RouterInterface $router;
    // private UrlGeneratorInterface $router; FIXME: better to use this

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    // Symfony appelle authenticate() seulement si supports() retourne true
    public function supports(Request $request): ?bool
    {
        // 1) toujours activer l'authentification CAS, indépendamment de la route demandée
        // return true; 
        // 2) déclenche l'authentification CAS que si l'on consulte explicitement la route '/login'
        return $request->attributes->get('_route') === 'app_login';
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        error_log('[CAS] Début de l’authentification CAS');

        if (session_status() === PHP_SESSION_NONE)
            session_start();

        CasHelper::init();
        // force CAS authentication
        \phpCAS::forceAuthentication();

        $username = \phpCAS::getUser();
        error_log('[CAS] authenticated user : ' . $username);

        // v2 (custom in-memory user)
        $passport = new SelfValidatingPassport(
            new UserBadge(
                $username,
                fn($id) =>
                new CasUser($id, ['ROLE_USER'], \phpCAS::getAttributes())
            )
        );

        return $passport;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        error_log('[CAS] call onAuthenticationSuccess()');
        return new RedirectResponse($this->router->generate('app_home'));
        // FIXME: use $targetpath to redirect to the asked page befaure auth.
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        error_log('[CAS] call onAuthenticationFailure()');
        // return new RedirectResponse('/');
        return new Response('Erreur CAS : ' . $exception->getMessage(), Response::HTTP_UNAUTHORIZED);

    }

}
