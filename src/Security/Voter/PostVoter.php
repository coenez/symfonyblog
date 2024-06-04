<?php

namespace App\Security\Voter;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class PostVoter extends Voter
{
    public function __construct(
        private readonly Security $security
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [Post::EDIT, Post::VIEW])
            && $subject instanceof Post;
    }

    /**
     * @param string $attribute
     * @param Post $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /**
         * @var User $user
         */
        $user = $token->getUser();
        $isAuthenticated = $user instanceof UserInterface;

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        switch ($attribute) {
            case Post::EDIT:
                return $isAuthenticated && (
                    $subject->getAuthor()->getId() === $user->getId() ||
                    $this->security->isGranted('ROLE_EDITOR')
                );

            case Post::VIEW:
                if (!$subject->isExtraPrivacy()) {
                    return true;
                }
                return $isAuthenticated && (
                    $subject->getAuthor()->getId() === $user->getId() ||
                    $subject->getAuthor()->getFollows()->contains($user)
                );
        }

        return false;
    }
}
