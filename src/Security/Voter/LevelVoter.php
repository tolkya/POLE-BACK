<?php

namespace App\Security\Voter;

use App\Entity\Level;
use App\Entity\User;
use App\Repository\UserActivityRepository;
use App\Repository\UserClubRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class LevelVoter extends Voter
{
    public const LEVEL_EDIT   = 'LEVEL_EDIT';
    public const LEVEL_DELETE = 'LEVEL_DELETE';
    public const SKILL_MANAGE = 'SKILL_MANAGE';

    public function __construct(
        private readonly UserClubRepository $userClubRepository,
        private readonly UserActivityRepository $userActivityRepository,
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::LEVEL_EDIT, self::LEVEL_DELETE, self::SKILL_MANAGE])
            && $subject instanceof Level;
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

        /** @var Level $subject */
        $club = $subject->getActivity()->getClub();

        $userClub = $this->userClubRepository->findOneBy([
            'member' => $user,
            'club'   => $club,
        ]);

        // Admin du club peut tout faire
        if ($userClub !== null && in_array('ADMIN', $userClub->getRoles())) {
            return true;
        }

        // Pour SKILL_MANAGE : le prof de l'activité peut aussi gérer les skills
        if ($attribute === self::SKILL_MANAGE) {
            $userActivity = $this->userActivityRepository->findOneBy([
                'member'   => $user,
                'activity' => $subject->getActivity(),
            ]);

            if ($userActivity !== null && $userActivity->getRole() === 'TEACHER') {
                return true;
            }
        }

        return false;
    }
}