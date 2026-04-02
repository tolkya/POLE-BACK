<?php

namespace App\Repository;

use App\Entity\Club;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Club>
 */
class ClubRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Club::class);
    }

    public function findByClubCode(string $clubCode): ?Club
    {
        if(!str_starts_with($clubCode, 'cde_')) {
            return null;
        }
        $id = (int) substr($clubCode, 4);
        if ($id <= 0) {
            return null;
        }
        return $this->find($id);
    }

    /**
     * @return Club[]
     */
    public function searchByName(string $name, int $limit = 5): array
    {
        return $this->createQueryBuilder('c')
            ->where('LOWER(c.name) LIKE LOWER(:name)')
            ->setParameter('name', '%' . $name . '%')
            ->orderBy(
                'CASE WHEN LOWER(c.name) LIKE LOWER(:startsWith) THEN 0 ELSE 1 END',
                'ASC'
            )
            ->addOrderBy('c.name', 'ASC')
            ->setParameter('startsWith', $name . '%')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
