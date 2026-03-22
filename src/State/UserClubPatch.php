<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\UserClub;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use App\Security\Voter\UserClubVoter;

class UserClubPatch implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Security $security,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): UserClub
    {
        /** @var UserClub $data */
        if (!$this->security->isGranted(UserClubVoter::EDIT, $data)) {
            throw new AccessDeniedHttpException('Accès refusé.');
        }

        $this->em->flush();

        return $data;
    }
}