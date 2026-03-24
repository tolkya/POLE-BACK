<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\ActivityRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class LevelsProvider implements ProviderInterface
{
    public function __construct(
        private readonly ActivityRepository $activityRepository,
        private readonly Security $security,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $activity = $this->activityRepository->find($uriVariables['activityId']);
        if ($activity === null) {
            throw new NotFoundHttpException('Activité introuvable.');
        }

        if (!$this->security->isGranted('CLUB_VIEW', $activity->getClub())) {
            throw new AccessDeniedHttpException('Vous n\'êtes pas membre de ce club.');
        }

        return $activity->getLevels()->toArray();
    }
}