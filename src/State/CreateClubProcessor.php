<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Club;
use App\Entity\User;
use App\Entity\UserClub;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class CreateClubProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Security $security,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Club
    {
        /** @var Club $club */
        $club = $data;

        /** @var User $user */
        $user = $this->security->getUser();

        // Créer le lien UserClub avec rôle ADMIN
        $userClub = new UserClub();
        $userClub->setMember($user);
        $userClub->setClub($club);
        $userClub->setRoles(['ADMIN']);

        $this->em->persist($club);
        $this->em->persist($userClub);
        $this->em->flush();

        return $club;
    }
}