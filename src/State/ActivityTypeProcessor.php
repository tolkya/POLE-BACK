<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\ActivityType;
use App\Repository\UserClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ActivityTypeProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Security $security,
        private readonly UserClubRepository $userClubRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ActivityType
    {
        $user = $this->security->getUser();

        // Seul un admin d'au moins un club peut créer un type d'activité
        $adminClubs = array_filter(
            $this->userClubRepository->findAllByUser($user),
            fn($uc) => in_array('ADMIN', $uc->getRoles())
        );

        if (empty($adminClubs) && !$this->security->isGranted('ROLE_SUPER_ADMIN')) {
            throw new AccessDeniedHttpException('Seul un administrateur de club peut créer un type d\'activité.');
        }

        $data->setCreatedBy($user);
        $this->em->persist($data);
        $this->em->flush();

        return $data;
    }
}