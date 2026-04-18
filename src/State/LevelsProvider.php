<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Enum\UserActivityStatus;
use App\Repository\ActivityRepository;
use App\Repository\LevelRepository;
use App\Repository\UserActivityRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class LevelsProvider implements ProviderInterface
{
    public function __construct(
        private readonly ActivityRepository     $activityRepository,
        private readonly UserActivityRepository $userActivityRepository,
        private readonly LevelRepository        $levelRepository,
        private readonly Security               $security,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $activity = $this->activityRepository->find($uriVariables['activityId']);
        if ($activity === null) {
            throw new NotFoundHttpException('Activité introuvable.');
        }

        // Admin du club → accès total
        if ($this->security->isGranted('CLUB_ADMIN', $activity->getClub())) {
            return $this->levelRepository->findByActivitySorted($activity->getId());
        }

        // Sinon : doit être APPROVED sur cette activité spécifique
        $user         = $this->security->getUser();
        $userActivity = $this->userActivityRepository->findOneBy([
            'member'   => $user,
            'activity' => $activity,
            'status'   => UserActivityStatus::APPROVED,
        ]);

        if ($userActivity === null) {
            throw new AccessDeniedHttpException('Vous devez être inscrit et validé pour accéder au contenu de cette activité.');
        }

        return $this->levelRepository->findByActivitySorted($activity->getId());
    }
}