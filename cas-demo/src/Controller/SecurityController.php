<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SecurityController extends AbstractController
{
    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - Symfony will intercept this route.');
    }

    #[Route('/cas-logout', name: 'app_cas_logout')]
    public function casLogout(): RedirectResponse
    {
        // CAS logout + retour vers page d'accueil
        \phpCAS::logoutWithRedirectService($this->generateUrl('app_home', [], true));
    }
}
