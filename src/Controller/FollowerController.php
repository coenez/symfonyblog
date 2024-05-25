<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class FollowerController extends AbstractController
{
    #[Route('/follow/{id<\d>}', name: 'app_follow')]
    #[IsGranted('ROLE_VERIFIED_USER')]
    public function follow(User $userToFollow, EntityManagerInterface $entityManager, Request $request): Response
    {
        /**
         * @var User $currentUser
         */
        $currentUser = $this->getUser();

        if ($currentUser->getId() === $userToFollow->getId()) {
            throw new \InvalidArgumentException('You are not allowed to follow yourself!');
        }

        if ($currentUser->getFollows()->contains($userToFollow)) {
            $currentUser->unFollow($userToFollow);
        } else {
            $currentUser->follow($userToFollow);
        }

        $entityManager->persist($currentUser);
        $entityManager->flush();

        return $this->redirect($request->headers->get('referer'));
    }
}
