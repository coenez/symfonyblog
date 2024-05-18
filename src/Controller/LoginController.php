<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function index(AuthenticationUtils $authUtils): Response
    {
        $lastUserName = $authUtils->getLastUsername();
        $error = $authUtils->getLastAuthenticationError();

        return $this->render('login/index.html.twig', [
            'lastUserName' => $lastUserName,
            'lastError' => $error
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout() {}
}
