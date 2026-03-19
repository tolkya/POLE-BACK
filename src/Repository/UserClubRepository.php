<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserClub;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserClub>
 */
class UserClubRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserClub::class);
    }

    /**
     * Retourne tous les UserClub d'un utilisateur.
     * @return UserClub[]
     */
    public function findAllByUser(User $user): array
    {
        return $this->createQueryBuilder('uc')
            ->where('uc.member = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}