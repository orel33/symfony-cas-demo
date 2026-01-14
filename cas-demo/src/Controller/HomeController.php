<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // Récupérer l'utilisateur si connecté
        $user = $this->getUser();
        $username = $user ? $user->getUserIdentifier() : 'Invité';
        $email = $user && method_exists($user, 'getAttribute') ? $user->getAttribute('mail') : '';

        return $this->render('home/index.html.twig', [
            'username' => $username,
            'email' => $email,
        ]);
    }
}
