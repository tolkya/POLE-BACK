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
}
