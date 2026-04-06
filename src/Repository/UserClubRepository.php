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
     * @param array{role?: string, search?: string, page?: int, limit?: int} $filters
     * @return UserClub[]
     */
    public function findByClub(Club $club, array $filters = []): array
    {
        $conn   = $this->getEntityManager()->getConnection();
        $limit  = min((int) ($filters['limit'] ?? 20), 100);
        $offset = (max((int) ($filters['page'] ?? 1), 1) - 1) * $limit;

        $sql    = 'SELECT uc.id FROM user_club uc JOIN "user" u ON u.id = uc.member_id WHERE uc.club_id = :clubId';
        $params = ['clubId' => $club->getId()];

        if (!empty($filters['role'])) {
            $sql .= ' AND CAST(uc.roles AS jsonb) @> CAST(:role AS jsonb)';
            $params['role'] = '["' . $filters['role'] . '"]';
        }

        if (!empty($filters['search'])) {
            $sql .= ' AND (LOWER(u.first_name) LIKE :search OR LOWER(u.last_name) LIKE :search OR LOWER(u.email) LIKE :search)';
            $params['search'] = '%' . strtolower($filters['search']) . '%';
        }

        $sql .= ' ORDER BY u.last_name ASC LIMIT ' . $limit . ' OFFSET ' . $offset;

        $rows = $conn->executeQuery($sql, $params)->fetchAllAssociative();
        $ids  = array_column($rows, 'id');
        if (empty($ids)) return [];

        return $this->createQueryBuilder('uc')
            ->join('uc.member', 'u')
            ->addSelect('u')
            ->where('uc.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('u.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le total de membres d'un club selon les mêmes filtres (sans LIMIT).
     * @param array{role?: string, search?: string} $filters
     */
    public function countByClub(Club $club, array $filters = []): int
    {
        $conn   = $this->getEntityManager()->getConnection();
        $sql    = 'SELECT COUNT(uc.id) FROM user_club uc JOIN "user" u ON u.id = uc.member_id WHERE uc.club_id = :clubId';
        $params = ['clubId' => $club->getId()];

        if (!empty($filters['role'])) {
            $sql .= ' AND CAST(uc.roles AS jsonb) @> CAST(:role AS jsonb)';
            $params['role'] = '["' . $filters['role'] . '"]';
        }

        if (!empty($filters['search'])) {
            $sql .= ' AND (LOWER(u.first_name) LIKE :search OR LOWER(u.last_name) LIKE :search OR LOWER(u.email) LIKE :search)';
            $params['search'] = '%' . strtolower($filters['search']) . '%';
        }

        return (int) $conn->executeQuery($sql, $params)->fetchOne();
    }    

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
     * @return UserClub[]
     */
    public function findAdminsByClub(Club $club): array
    {
        $results = $this->createQueryBuilder('uc')
            ->join('uc.member', 'u')
            ->addSelect('u')
            ->where('uc.club = :club')
            ->setParameter('club', $club)
            ->getQuery()
            ->getResult();

        return array_values(array_filter(
            $results,
            fn(UserClub $uc) => in_array('ADMIN', $uc->getRoles())
        ));
    }
}