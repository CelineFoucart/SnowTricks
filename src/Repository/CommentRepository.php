<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Trick;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 *
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public const PER_PAGE = 15;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function save(Comment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Comment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Comment[] Returns an array of Comment objects
     */
    public function findPaginated(Trick $trick, int $offset = 0, ?int $limit = null): array
    {
        if (null === $limit) {
            $limit = self::PER_PAGE;
        }
        return $this->createQueryBuilder('c')
            ->orderBy('c.createdAt', 'DESC')
            ->andWhere('c.trick = :trick')
            ->setParameter('trick', $trick)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function countCommentByTrick(Trick $trick): int
    {
        $results = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.trick = :trick')
            ->setParameter('trick', $trick)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return $results;
    }
}
