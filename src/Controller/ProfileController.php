<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfileController extends AbstractController
{
    #[Route('/profile/{id<\d>}', name: 'app_profile')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function show(User $user, PostRepository $postRepository): Response
    {
        return $this->render('profile/show.html.twig', [
            'user' => $user,
            'posts' => $postRepository->findAllByAuthor([$user->getId()]),
        ]);
    }

    #[Route('/profile/{id<\d>}/follows', name: 'app_profile_follows')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function follows(User $user): Response
    {
        return $this->render('profile/follows.html.twig', [
            'user' => $user ?? null
        ]);
    }

    #[Route('/profile/{id<\d>}/followers', name: 'app_profile_followers')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function followers(User $user): Response
    {
        return $this->render('profile/followers.html.twig', [
            'user' => $user ?? null
        ]);
    }
}
