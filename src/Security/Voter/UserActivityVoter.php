<?php

namespace App\Security\Voter;

use App\Entity\UserActivity;
use App\Entity\User;
use App\Enum\ActivityRole;
use App\Repository\UserActivityRepository;
use App\Repository\UserClubRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

final class UserActivityVoter extends Voter
{
    public const MANAGE = 'ACTIVITY_MEMBER_MANAGE';
    public const SELF_LEAVE = 'ACTIVITY_SELF_LEAVE';

    public function __construct(
        private readonly UserClubRepository $userClubRepository,
        private readonly UserActivityRepository $userActivityRepository,
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::MANAGE, self::SELF_LEAVE])
            && $subject instanceof UserActivity;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        // Le membre peut se désinscrire lui-même
        if ($attribute === self::SELF_LEAVE && $subject->getMember() === $user) {
            return true;
        }

        // Super Admin bypass
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            return true;
        }

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