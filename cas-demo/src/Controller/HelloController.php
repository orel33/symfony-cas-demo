<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HelloController extends AbstractController
{
    #[Route('/hello', name: 'app_hello')]
    public function hello(): Response
    {
        // direct response for simplicity, without using a Twig template
        // $username = \phpCAS::getUser();
        $user = $this->getUser();
        $username = $user->getUserIdentifier();
        $displayname = $user->getAttribute('displayName');   // attribut CAS optionnel
        $email = $user->getAttribute('mail');                // attribut CAS optionnel

        return new Response("<h1>Welcome $displayname</h1> login: $username <br> email: $email");
        //  return new Response('<h1>Hello ' . htmlspecialchars($username) . ' !</h1>');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \Exception('Cette méthode peut rester vide.');
    }
}



