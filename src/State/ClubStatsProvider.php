<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\ClubStats;
use App\Repository\ActivityRepository;
use App\Repository\ClubRepository;
use App\Repository\UserClubRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ClubStatsProvider implements ProviderInterface
{
    public function __construct(
        private readonly ClubRepository $clubRepository,
        private readonly UserClubRepository $userClubRepository,
        private readonly ActivityRepository $activityRepository,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ClubStats
    {
        $club = $this->clubRepository->find($uriVariables['id']);
        if ($club === null) {
            throw new NotFoundHttpException('Club introuvable.');
        }

        $members = $this->userClubRepository->findByClub($club);

        $membersCount   = count($members);
        $teachersCount  = count(array_filter($members, fn($uc) => in_array('TEACHER', $uc->getRoles())));
        $activitiesCount = count($this->activityRepository->findBy(['club' => $club]));

        return new ClubStats($membersCount, $activitiesCount, $teachersCount);
    }
}