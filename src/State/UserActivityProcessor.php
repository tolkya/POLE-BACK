<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\UserActivity;
use App\Enum\ActivityRole;
use App\Enum\UserActivityStatus;
use App\Repository\ActivityRepository;
use App\Repository\UserActivityRepository;
use App\Repository\UserClubRepository;
use App\Repository\UserRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class UserActivityProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Security $security,
        private readonly ActivityRepository $activityRepository,
        private readonly UserRepository $userRepository,
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

        $club = $activity->getClub();

        // Seul l'admin peut inscrire directement
        if (!$this->security->isGranted('CLUB_ADMIN', $club)) {
            throw new AccessDeniedHttpException('Seul l\'administrateur du club peut inscrire un membre directement.');
        }

        // Résoudre le membre cible
        $memberId = $data->getMemberId();
        $member = $this->userRepository->find($memberId);
        if ($member === null) {
            throw new NotFoundHttpException('Utilisateur introuvable.');
        }

        // Vérifier que le membre cible appartient au club
        $userClub = $this->userClubRepository->findOneBy(['member' => $member, 'club' => $club]);
        if ($userClub === null) {
            throw new UnprocessableEntityHttpException('Cet utilisateur n\'est pas membre de ce club.');
        }

        // Vérifier le rôle demandé selon les rôles dans UserClub
        $role = $data->getRole();
        if ($role === ActivityRole::TEACHER) {
            $allowedRoles = ['TEACHER', 'ADMIN'];
            if (empty(array_intersect($allowedRoles, $userClub->getRoles()))) {
                throw new UnprocessableEntityHttpException('Cet utilisateur doit avoir le rôle TEACHER ou ADMIN dans le club pour être inscrit comme professeur.');
            }
        } else {
            // STUDENT — doit avoir MEMBER dans UserClub
            if (!in_array('MEMBER', $userClub->getRoles())) {
                throw new UnprocessableEntityHttpException('Cet utilisateur doit avoir le rôle MEMBER dans le club pour être inscrit comme élève.');
            }
        }

        // Vérifier l'unicité
        $existing = $this->userActivityRepository->findOneBy(['member' => $member, 'activity' => $activity]);
        if ($existing !== null) {
            throw new ConflictHttpException('Cet utilisateur est déjà inscrit à cette activité.');
        }

        $userActivity = new UserActivity();
        $userActivity->setMember($member);
        $userActivity->setActivity($activity);
        $userActivity->setRole($role);
        $userActivity->setStatus(UserActivityStatus::APPROVED);

        $this->em->persist($userActivity);
        $this->em->flush();

        $this->notificationService->notifyActivityJoinApproved($activity, $member);

        return $userActivity;
    }
}