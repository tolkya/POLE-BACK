<?php

namespace App\Repository;

use App\Entity\UserActivity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;

/**
 * @extends ServiceEntityRepository<UserActivity>
 */
class UserActivityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserActivity::class);
    }
/**
 * Retourne les UserActivity de l'utilisateur connecté, optionnellement filtrés par club.
 * @return UserActivity[]
 */
    public function findByMemberAndClub(User $member, ?int $clubId = null): array
    {
        $qb = $this->createQueryBuilder('ua')
            ->join('ua.activity', 'a')
            ->addSelect('a')
            ->where('ua.member = :member')
            ->setParameter('member', $member);

        if ($clubId !== null) {
            $qb->join('a.club', 'c')
            ->andWhere('c.id = :clubId')
            ->setParameter('clubId', $clubId);
        }

        return $qb->getQuery()->getResult();
    }
}
