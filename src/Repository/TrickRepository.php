<?php

namespace App\Repository;

use App\Entity\Trick;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Trick>
 *
 * @method Trick|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trick|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trick[]    findAll()
 * @method Trick[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrickRepository extends ServiceEntityRepository
{
    private int $perPage = 12;

    /**
     * @param string $perPageTrick the number of tricks per page, defined in the .env
     */
    public function __construct(ManagerRegistry $registry, string $perPageTrick)
    {
        parent::__construct($registry, Trick::class);
        $this->perPage = $perPageTrick;
    }

    /**
     * Persists an entity.
     */
    public function save(Trick $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Removes an entity.
     */
    public function remove(Trick $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Returns an array of Trick objects.
     *
     * @return Trick[]
     */
    public function findPaginated(int $offset = 0, ?int $limit = null): array
    {
        if (null === $limit) {
            $limit = $this->perPage;
        }

        return $this->createQueryBuilder('t')
            ->orderBy('t.name', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Finds a trick by his slug.
     */
    public function findOneBySlug(string $slug): ?Trick
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
