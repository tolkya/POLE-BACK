<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\User;
use App\Enum\ActivityRole;
use App\Repository\ActivityRepository;
use App\Repository\UserActivityRepository;
use App\Repository\UserClubRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ActivityMembersProvider implements ProviderInterface
{
    public function __construct(
        private readonly ActivityRepository $activityRepository,
        private readonly UserActivityRepository $userActivityRepository,
        private readonly UserClubRepository $userClubRepository,
        private readonly Security $security,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $activity = $this->activityRepository->find($uriVariables['activityId']);
        if ($activity === null) {
            throw new NotFoundHttpException('Activité introuvable.');
        }

        /** @var User|null $user */
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new AccessDeniedHttpException();
        }

        // Super Admin
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            return $this->userActivityRepository->findBy(
                ['activity' => $activity],
                ['createdAt' => 'ASC']
            );
        }

        // Admin du club
        $userClub = $this->userClubRepository->findOneBy([
            'member' => $user,
            'club'   => $activity->getClub(),
        ]);
        if ($userClub !== null && in_array('ADMIN', $userClub->getRoles())) {
            return $this->userActivityRepository->findBy(
                ['activity' => $activity],
                ['createdAt' => 'ASC']
            );
        }

        // Teacher de l'activité
        $userActivity = $this->userActivityRepository->findOneBy([
            'member'   => $user,
            'activity' => $activity,
        ]);
        if ($userActivity !== null && $userActivity->getRole() === ActivityRole::TEACHER) {
            return $this->userActivityRepository->findBy(
                ['activity' => $activity],
                ['createdAt' => 'ASC']
            );
        }

        throw new AccessDeniedHttpException('Réservé au Teacher ou à l\'Admin du club.');
    }
}
