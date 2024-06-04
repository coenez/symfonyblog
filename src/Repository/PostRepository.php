<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function findAllWithComments(): array
    {
        return $this->findAllQuery(withComments: true, withLikes: true, withAuthors: true)->getQuery()->getResult();
    }

    public function findAllByAuthor(Collection|array $authors): array
    {
        return $this->findAllQuery(withComments: true, withLikes: true, withProfiles: true)
            ->where('p.author IN(:authors)')
            ->setParameter('authors', $authors)
            ->getQuery()
            ->getResult();
    }

    public function findTopLiked(int $minLikes): array
    {
        $ids = $this->findAllQuery(withLikes: true)
            ->select('p.id')
            ->groupBy('p.id')
            ->having('COUNT(l) >= :minLikes')
            ->setParameter('minLikes', $minLikes)
            ->getQuery()
            ->getResult(Query::HYDRATE_SCALAR_COLUMN);

        return $this->findAllQuery(withComments: true, withLikes: true, withAuthors: true, withProfiles: true)
            ->where('p.id IN(:idList)')
            ->setParameter('idList', $ids)
            ->getQuery()
            ->getResult();
    }

    private function findAllQuery(bool $withComments = false, bool $withLikes = false, bool $withAuthors = false, bool $withProfiles = false): QueryBuilder
    {
        $query = $this->createQueryBuilder('p');

        if ($withComments) {
            $query->leftJoin('p.comments', 'c')
                ->addSelect('c');
        }

        if ($withLikes) {
            $query->leftJoin('p.likedBy', 'l')
                ->addSelect('l');
        }

        if ($withAuthors || $withProfiles) {
            $query->leftJoin('p.author', 'a')
                ->addSelect('a');
        }

        if ($withProfiles) {
            $query->leftJoin('a.userProfile', 'up')
                ->addSelect('up');
        }

        return $query->orderBy('p.created', 'DESC');
    }
}
