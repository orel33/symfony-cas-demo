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
        $user = $this->getUser();
        $username = $user ? $user->getUserIdentifier() : null;
        $email = $user && method_exists($user, 'getAttribute') ? $user->getAttribute('mail') : null;

        return $this->render('home/index.html.twig', [
            'username' => $username,
            'email' => $email,
        ]);
    }

    #[Route('/public', name: 'app_public')]
    public function publicPage(): Response
    {
        return new Response('<h1>Page publique - accessible sans login</h1>');
    }

    #[Route('/private', name: 'app_private')]
    public function privatePage(): Response
    {
        return new Response('<h1>Page privée - nécessite login CAS</h1>');
    }
}
