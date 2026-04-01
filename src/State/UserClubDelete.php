<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\UserClub;
use App\Repository\UserClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use App\Security\Voter\UserClubVoter;

class UserClubDelete implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Security $security,
        private readonly UserClubRepository $userClubRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        /** @var UserClub $data */
        $canDelete = $this->security->isGranted(UserClubVoter::DELETE, $data);
        $canLeave  = $this->security->isGranted(UserClubVoter::SELF_LEAVE, $data);

        if (!$canDelete && !$canLeave) {
            throw new AccessDeniedHttpException('Accès refusé.');
        }

        // Protection : ne pas supprimer le dernier ADMIN du club
        if (in_array('ADMIN', $data->getRoles())) {
            $admins = array_filter(
                $this->userClubRepository->findByClub($data->getClub()),
                fn(UserClub $uc) => in_array('ADMIN', $uc->getRoles())
            );

            if (count($admins) <= 1) {
                throw new UnprocessableEntityHttpException('Impossible de retirer le dernier administrateur du club.');
            }
        }

        $this->em->remove($data);
        $this->em->flush();
    }
}