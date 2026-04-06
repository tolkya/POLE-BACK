<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Club;
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

    /**
     * Retourne tous les UserClub d'un club, avec filtres optionnels.
     * @param array{role?: string, search?: string} $filters
     * @return UserClub[]
     */
    public function findByClub(Club $club, array $filters = []): array
    {
        $qb = $this->createQueryBuilder('uc')
            ->join('uc.member', 'u')
            ->addSelect('u')
            ->where('uc.club = :club')
            ->setParameter('club', $club);

        if (!empty($filters['role'])) {
            $qb->andWhere('uc.roles LIKE :role')
               ->setParameter('role', '%"' . $filters['role'] . '"%');
        }

        if (!empty($filters['search'])) {
            $qb->andWhere('LOWER(u.firstName) LIKE :search OR LOWER(u.lastName) LIKE :search OR LOWER(u.email) LIKE :search')
               ->setParameter('search', '%' . strtolower($filters['search']) . '%');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Compte les membres en attente de validation manuelle (validatedAt IS NULL).
     */
    public function countPendingMembers(Club $club): int
    {
        return (int) $this->createQueryBuilder('uc')
            ->select('COUNT(uc.id)')
            ->where('uc.club = :club')
            ->andWhere('uc.validatedAt IS NULL')
            ->setParameter('club', $club)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Retourne uniquement les UserClub dont le membre a le rôle ADMIN dans ce club.
     * @return UserClub[]
     */
    public function findAdminsByClub(Club $club): array
    {
        return $this->createQueryBuilder('uc')
            ->join('uc.member', 'u')
            ->addSelect('u')
            ->where('uc.club = :club')
            ->andWhere('uc.roles LIKE :role')
            ->setParameter('club', $club)
            ->setParameter('role', '%"ADMIN"%')
            ->getQuery()
            ->getResult();
    }
}