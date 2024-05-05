<?php

namespace App\DataFixtures;

use App\Entity\Post;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    private array $postData = [
        [
            'title' => 'Summary of releases of this month',
            'text' => 'This month was stacked with interesting releases. We got new albums from Fractal gates, Illdisposed and Temple of void. Also new single from Dark Tranquility!.',
            'created' => '2024-04-22',
        ],
        [
            'title' => 'Fractal gates released new album',
            'text' => 'Fractal gates, the melodic death metal powerhouse from France, has released their new album: One with dawn. Get ready for an intergalactic melodic pummeling because this one is going on the end of the year list for sure.',
            'created' => '2024-04-12',
        ],
        [
            'title' => 'Slipknot reveals new drummer identity',
            'text' => 'Slipknot has officialy revealed the identity of their new drummer. This is no surprise because the community had already figured it out but its nice to have it confirmed. Congratulations to Eloy Casagrande! He has big shoes to fill but if anyone can do it, its him alright. ',
            'created' => '2024-04-28',
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach($this->postData as $row) {
            $post = new Post();
            $post->setTitle($row['title']);
            $post->setText($row['text']);
            $post->setCreated(new \DateTime($row['created']));
            $manager->persist($post);
        }

        $manager->flush();
    }
}
