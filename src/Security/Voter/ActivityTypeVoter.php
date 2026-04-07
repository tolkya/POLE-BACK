<?php

namespace App\Security\Voter;

use App\Entity\ActivityType;
use App\Entity\User;
use App\Repository\UserClubRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class ActivityTypeVoter extends Voter
{
    public const CREATE = 'ACTIVITY_TYPE_CREATE';

    public function __construct(private readonly UserClubRepository $userClubRepository) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        // Ce Voter gère uniquement CREATE_ACTIVITY_TYPE, peu importe le subject
        return $attribute === self::CREATE;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        // L'utilisateur est-il admin d'au moins un club ?
        return $this->userClubRepository->isAdminOfAnyClub($user);
    }
}