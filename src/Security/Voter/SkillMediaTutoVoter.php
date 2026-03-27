<?php

namespace App\Security\Voter;

use App\Entity\SkillMediaTuto;
use App\Entity\User;
use App\Repository\UserClubRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class SkillMediaTutoVoter extends Voter
{
    public const SKILL_MEDIA_TUTO_DELETE = 'SKILL_MEDIA_TUTO_DELETE';

    public function __construct(
        private readonly UserClubRepository $userClubRepository,
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::SKILL_MEDIA_TUTO_DELETE
            && $subject instanceof SkillMediaTuto;
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

        /** @var SkillMediaTuto $subject */
        $club = $subject->getSkill()->getLevel()->getActivity()->getClub();

        $userClub = $this->userClubRepository->findOneBy([
            'member' => $user,
            'club'   => $club,
        ]);

        // Admin du club peut tout supprimer
        if ($userClub !== null && in_array('ADMIN', $userClub->getRoles())) {
            return true;
        }

        // Le créateur du tuto peut le supprimer
        return $subject->getCreatedBy()?->getId() === $user->getId();
    }
}
