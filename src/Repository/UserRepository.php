<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Retourne tous les utilisateurs ayant le rôle ROLE_SUPER_ADMIN.
     *
     * On utilise une native query avec CAST car PostgreSQL ne supporte pas LIKE
     * directement sur une colonne de type `json`. On caste en TEXT avant comparaison.
     *
     * @return User[]
     */
    public function findSuperAdmins(): array
    {
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(User::class, 'u');

        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT u.* FROM "user" u WHERE CAST(u.roles AS TEXT) LIKE :role',
                $rsm
            )
            ->setParameter('role', '%"ROLE_SUPER_ADMIN"%')
            ->getResult();
    }
}
