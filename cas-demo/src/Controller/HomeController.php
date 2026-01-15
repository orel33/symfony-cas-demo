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
        $email = $user ? $user->getAttribute('mail') : null;

        // return $this->render('home/index.html.twig', [
        //     'username' => $username,
        //     'email' => $email,
        // ]);

        return new Response("<h1>Home Page - Welcome</h1> login: $username <br> email: $email");

    }

    #[Route('/login', name: 'app_login')]
    public function login(): never
    {
        // should be never called...
        throw new \LogicException('Handled by CAS authenticator.');
    }

    #[Route('/public', name: 'app_public')]
    public function publicPage(): Response
    {
        return new Response('<h1>Public Page - Welcome</h1> bla bla bla...');
    }

    #[Route('/private', name: 'app_private')]
    public function privatePage(): Response
    {
        $user = $this->getUser();
        $username = $user ? $user->getUserIdentifier() : null;
        $email = $user ? $user->getAttribute('mail') : null;
        return new Response("<h1>Private Page - Welcome</h1> login: $username <br> email: $email");

    }
}
