<?php

namespace App\Security\Voter;

use App\Entity\UserActivity;
use App\Entity\User;
use App\Enum\ActivityRole;
use App\Repository\ActivityRepository;
use App\Repository\UserActivityRepository;
use App\Repository\UserClubRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

final class UserActivityVoter extends Voter
{
    public const MANAGE     = 'ACTIVITY_MEMBER_MANAGE';
    public const MANAGE_ANY = 'ACTIVITY_MEMBER_MANAGE_ANY';
    public const SELF_LEAVE = 'ACTIVITY_SELF_LEAVE';

    public function __construct(
        private readonly UserClubRepository $userClubRepository,
        private readonly UserActivityRepository $userActivityRepository,
        private readonly ActivityRepository $activityRepository,
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        if ($attribute === self::MANAGE_ANY) {
            return is_int($subject);
        }

        return in_array($attribute, [self::MANAGE, self::SELF_LEAVE])
            && $subject instanceof UserActivity;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        // Super Admin bypass
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            return true;
        }

        // ── MANAGE_ANY : sujet = activityId (int) ─────────────────────────
        if ($attribute === self::MANAGE_ANY) {
            $activity = $this->activityRepository->find($subject);
            if ($activity === null) {
                return false;
            }
            $userClub = $this->userClubRepository->findOneBy([
                'member' => $user,
                'club'   => $activity->getClub(),
            ]);
            if ($userClub !== null && in_array('ADMIN', $userClub->getRoles())) {
                return true;
            }
            $userActivity = $this->userActivityRepository->findOneBy([
                'member'   => $user,
                'activity' => $activity,
            ]);
            return $userActivity !== null && $userActivity->getRole() === ActivityRole::TEACHER;
        }

        // ── SELF_LEAVE ─────────────────────────────────────────────────────
        if ($attribute === self::SELF_LEAVE && $subject->getMember() === $user) {
            return true;
        }

        // ── MANAGE : sujet = UserActivity ─────────────────────────────────
        /** @var UserActivity $subject */
        $club = $subject->getActivity()->getClub();

        // Admin du club peut tout faire
        $userClub = $this->userClubRepository->findOneBy([
            'member' => $user,
            'club'   => $club,
        ]);

        if ($userClub !== null && in_array('ADMIN', $userClub->getRoles())) {
            return true;
        }

        // Prof de l'activité peut aussi gérer les inscriptions
        $userActivity = $this->userActivityRepository->findOneBy([
            'member'   => $user,
            'activity' => $subject->getActivity(),
        ]);

        if ($userActivity !== null && $userActivity->getRole() === ActivityRole::TEACHER) {
            return true;
        }

        return false;
    }
}