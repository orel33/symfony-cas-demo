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
        // Cette méthode est interceptée par Symfony. Elle doit rester vide.
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/cas-logout', name: 'app_cas_logout')]
    public function casLogout(): RedirectResponse
    {
        // Déconnexion CAS
        // A) logout avec redirection
        // \phpCAS::logoutWithRedirectService('https://promo-st.emi.u-bordeaux.fr/');
        // \phpCAS::logoutWithRedirectService($this->generateUrl('home', [], true));
        
        // B) Ou juste \phpCAS::logout() pour rediriger vers le logout CAS par défaut
        \phpCAS::logout();
    }
}
