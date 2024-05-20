<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LikeController extends AbstractController
{
    #[Route('/like/{id<\d>}', name: 'app_post_like')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function like(Post $post, EntityManagerInterface $entityManager, Request $request): Response
    {
        /**
         * @var User $currentUser
         */
        $currentUser = $this->getUser();

        if ($post->getLikedBy()->contains($currentUser)) {
            $post->removeLikedBy($currentUser);
        } else {
            $post->addLikedBy($currentUser);
        }

        $entityManager->persist($post);
        $entityManager->flush();

        return $this->redirect($request->headers->get('referer'));
    }
}
