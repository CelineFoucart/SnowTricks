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

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /**
     * Persists an entity.
     * 
     * @param Comment $entity
     * @param bool $flush
     * 
     * @return void
     */
    public function save(Comment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Removes an entity.
     * 
     * @param Comment $entity
     * @param bool $flush
     * 
     * @return void
     */
    public function remove(Comment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Returns an array of Comment objects.
     * 
     * @return Comment[] 
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

    /**
     * Counts the comments of a trick.
     * @param Trick $trick
     * 
     * @return int
     */
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
