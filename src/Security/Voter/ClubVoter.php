<?php

namespace App\Security\Voter;

use App\Entity\Club;
use App\Entity\User;
use App\Repository\UserClubRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

final class ClubVoter extends Voter
{
    public const ADMIN = 'CLUB_ADMIN';
    public const VIEW = 'CLUB_VIEW';

    public function __construct(
        private readonly UserClubRepository $userClubRepository,
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::ADMIN, self::VIEW])
            && $subject instanceof Club;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Club $club */
        $club = $subject;

        return match ($attribute) {
            self::ADMIN => $this->isClubAdmin($user, $club),
            self::VIEW => $this->isClubMember($user, $club),
            default => false,
        };
    }

    private function isClubAdmin(User $user, Club $club): bool
    {
        $userClub = $this->userClubRepository->findOneBy([
            'member' => $user,
            'club' => $club,
        ]);

        return $userClub !== null && in_array('ADMIN', $userClub->getRoles());
    }

    private function isClubMember(User $user, Club $club): bool
    {
        $userClub = $this->userClubRepository->findOneBy([
            'member' => $user,
            'club'   => $club,
        ]);

        return $userClub !== null && $userClub->getValidatedAt() !== null;
    }
}