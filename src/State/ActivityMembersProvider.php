<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\UserActivity;
use App\Repository\ActivityRepository;
use App\Repository\UserActivityRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ActivityMembersProvider implements ProviderInterface
{
    public function __construct(
        private readonly ActivityRepository $activityRepository,
        private readonly UserActivityRepository $userActivityRepository,
        private readonly Security $security,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $activity = $this->activityRepository->find($uriVariables['activityId']);
        if ($activity === null) {
            throw new NotFoundHttpException('Activité introuvable.');
        }

        $club = $activity->getClub();

        // Accessible par l'admin du club OU un prof de l'activité
        if (!$this->security->isGranted('CLUB_ADMIN', $club) && !$this->security->isGranted('CLUB_VIEW', $club)) {
            throw new AccessDeniedHttpException('Accès refusé.');
        }

        return $this->userActivityRepository->findBy(
            ['activity' => $activity],
            ['createdAt' => 'ASC']
        );
    }
}