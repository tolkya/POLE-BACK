<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\UserActivity;
use App\Entity\User;
use App\Enum\ActivityRole;
use App\Enum\UserActivityStatus;
use App\Repository\ActivityRepository;
use App\Repository\UserActivityRepository;
use App\Repository\UserClubRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class ActivityJoinProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Security $security,
        private readonly ActivityRepository $activityRepository,
        private readonly UserClubRepository $userClubRepository,
        private readonly UserActivityRepository $userActivityRepository,
        private readonly NotificationService $notificationService,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): UserActivity
    {
        $activity = $this->activityRepository->find($uriVariables['activityId']);
        if ($activity === null) {
            throw new NotFoundHttpException('Activité introuvable.');
        }

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new AccessDeniedHttpException('Vous devez être connecté.');
        }

        $club = $activity->getClub();

        // Vérifier que l'utilisateur est MEMBER dans ce club
        $userClub = $this->userClubRepository->findOneBy(['member' => $user, 'club' => $club]);
        if ($userClub === null || !in_array('MEMBER', $userClub->getRoles())) {
            throw new UnprocessableEntityHttpException('Vous devez avoir le rôle MEMBER dans ce club pour vous inscrire à une activité.');
        }

        // Vérifier l'unicité
        $existing = $this->userActivityRepository->findOneBy(['member' => $user, 'activity' => $activity]);
        if ($existing !== null) {
            throw new ConflictHttpException('Vous êtes déjà inscrit à cette activité.');
        }

        $userActivity = new UserActivity();
        $userActivity->setMember($user);
        $userActivity->setActivity($activity);
        $userActivity->setRole(ActivityRole::STUDENT);
        $userActivity->setStatus(UserActivityStatus::PENDING);

        $this->em->persist($userActivity);
        $this->em->flush();

        // Notifier les admins + profs de l'activité
        $this->notificationService->notifyActivityJoinRequest($activity, $user);

        return $userActivity;
    }
}