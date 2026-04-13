<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\ClubAdminStats;
use App\Repository\ActivityRepository;
use App\Repository\ClubRepository;
use App\Repository\NotificationReceiptRepository;
use App\Repository\UserActivityRepository;
use App\Repository\UserClubRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ClubAdminStatsProvider implements ProviderInterface
{
    public function __construct(
        private readonly ClubRepository $clubRepository,
        private readonly UserClubRepository $userClubRepository,
        private readonly ActivityRepository $activityRepository,
        private readonly UserActivityRepository $userActivityRepository,
        private readonly NotificationReceiptRepository $notificationReceiptRepository,
        private readonly Security $security,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ClubAdminStats
    {
        $club = $this->clubRepository->find($uriVariables['id']);
        if ($club === null) {
            throw new NotFoundHttpException('Club introuvable.');
        }

        if (!$this->security->isGranted('CLUB_ADMIN', $club)) {
            throw new AccessDeniedHttpException('Accès réservé aux administrateurs du club.');
        }

        $user    = $this->security->getUser();
        $members = $this->userClubRepository->findByClub($club);

        $validatedMembers = array_filter($members, fn($uc) => $uc->getValidatedAt() !== null);
        $membersCount     = count($validatedMembers);
        $teachersCount    = count(array_filter($validatedMembers, fn($uc) => in_array('TEACHER', $uc->getRoles())));
        $activitiesCount     = count($this->activityRepository->findBy(['club' => $club]));
        $pendingEnrollments  = $this->userActivityRepository->countPendingByClub($club);
        $pendingMembers      = $this->userClubRepository->countPendingMembers($club);
        $unreadNotifications = $this->notificationReceiptRepository->countUnreadForUser($user);

        return new ClubAdminStats(
            membersCount:        $membersCount,
            teachersCount:       $teachersCount,
            activitiesCount:     $activitiesCount,
            pendingEnrollments:  $pendingEnrollments,
            pendingMembers:      $pendingMembers,
            unreadNotifications: $unreadNotifications,
        );
    }
}
