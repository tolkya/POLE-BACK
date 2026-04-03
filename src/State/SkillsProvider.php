<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Enum\UserActivityStatus;
use App\Repository\LevelRepository;
use App\Repository\UserActivityRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class SkillsProvider implements ProviderInterface
{
    public function __construct(
        private readonly LevelRepository $levelRepository,
        private readonly UserActivityRepository $userActivityRepository,
        private readonly Security $security,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $level = $this->levelRepository->find($uriVariables['levelId']);
        if ($level === null) {
            throw new NotFoundHttpException('Niveau introuvable.');
        }

        $activity = $level->getActivity();

        // Admin du club → accès total
        if ($this->security->isGranted('CLUB_ADMIN', $activity->getClub())) {
            return $level->getSkills()->toArray();
        }

        // Sinon : doit être APPROVED sur cette activité
        $user = $this->security->getUser();
        $userActivity = $this->userActivityRepository->findOneBy([
            'member'   => $user,
            'activity' => $activity,
            'status'   => UserActivityStatus::APPROVED,
        ]);

        if ($userActivity === null) {
            throw new AccessDeniedHttpException('Vous devez être inscrit et validé pour accéder aux compétences de cette activité.');
        }

        return $level->getSkills()->toArray();
    }
}