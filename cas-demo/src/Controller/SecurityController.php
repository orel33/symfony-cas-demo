<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\Security\CasHelper;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(): never
    {
        // should be never called...
        throw new \LogicException('Handled by CAS authenticator.');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - Symfony will intercept this route.');
    }

    #[Route('/cas-logout', name: 'app_cas_logout')]
    public function casLogout(): RedirectResponse
    {
        error_log('[CAS] call casLogout()');
        // CAS logout and redirect
        CasHelper::init();
        // \phpCAS::logoutWithRedirectService($this->generateUrl('app_home', [], true));
        \phpCAS::logoutWithRedirectService($_ENV['CAS_SERVICE_URL']);

        // jamais atteint, mais pour Symfony
        return new RedirectResponse('/');
    }
}
