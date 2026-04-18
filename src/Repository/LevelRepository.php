<?php

namespace App\Repository;

use App\Entity\Level;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Level>
 */
class LevelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Level::class);
    }

    /**
     * Retourne les levels d'une activité triés par position ASC.
     *
     * @return Level[]
     */
    public function findByActivitySorted(int $activityId): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.activity = :activityId')
            ->setParameter('activityId', $activityId)
            ->orderBy('l.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne la position maximale des levels d'une activité.
     * Retourne -1 si aucun level n'existe (le prochain sera à 0).
     */
    public function getMaxPosition(int $activityId): int
    {
        $result = $this->createQueryBuilder('l')
            ->select('MAX(l.position)')
            ->where('l.activity = :activityId')
            ->setParameter('activityId', $activityId)
            ->getQuery()
            ->getSingleScalarResult();

        return $result !== null ? (int) $result : -1;
    }
}