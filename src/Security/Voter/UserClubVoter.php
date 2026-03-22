<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\UserClub;
use App\Repository\UserClubRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class UserClubVoter extends Voter
{
    public const EDIT   = 'USER_CLUB_EDIT';
    public const DELETE = 'USER_CLUB_DELETE';

    public function __construct(
        private readonly UserClubRepository $userClubRepository,
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE])
            && $subject instanceof UserClub;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var UserClub $userClub */
        $userClub = $subject;

        // Le Super Admin peut tout faire
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            return true;
        }

        return match ($attribute) {
            self::EDIT, self::DELETE => $this->isClubAdmin($user, $userClub),
            default => false,
        };
    }

    private function isClubAdmin(User $user, UserClub $userClub): bool
    {
        $adminUserClub = $this->userClubRepository->findOneBy([
            'member' => $user,
            'club'   => $userClub->getClub(),
        ]);

        return $adminUserClub !== null && in_array('ADMIN', $adminUserClub->getRoles());
    }
}