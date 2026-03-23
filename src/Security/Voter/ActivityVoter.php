<?php

namespace App\Security\Voter;

use App\Entity\Activity;
use App\Entity\User;
use App\Repository\UserClubRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

final class ActivityVoter extends Voter
{
    public const EDIT   = 'ACTIVITY_EDIT';
    public const DELETE = 'ACTIVITY_DELETE';

    public function __construct(
        private readonly UserClubRepository $userClubRepository,
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE])
            && $subject instanceof Activity;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        // Super Admin peut tout faire
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            return true;
        }

        /** @var Activity $subject */
        $userClub = $this->userClubRepository->findOneBy([
            'member' => $user,
            'club'   => $subject->getClub(),
        ]);

        if ($userClub === null) {
            return false;
        }

        return in_array('ADMIN', $userClub->getRoles());
    }
}