<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private array $userData = [
        [
            'email' => 'test@test.nl',
            'password' => 'test',
        ],
        [
            'email' => 'admin@test.nl',
            'password' => 'test',
        ],
        [
            'email' => 'test2@test.nl',
            'password' => 'test',
        ],
    ];

    /**
     * @param UserPasswordHasherInterface $userPasswordHasher\
     */
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher
    ){}

    public function load(ObjectManager $manager): void
    {
        foreach($this->userData as $row) {
            $user = new User();
            $user->setEmail($row['email']);
            $user->setPassword($this->userPasswordHasher->hashPassword(
                $user,
                $row['password']
            ));
            $manager->persist($user);
        }

        $manager->flush();
    }
}
