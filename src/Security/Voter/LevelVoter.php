<?php

namespace App\Security\Voter;

use App\Entity\Level;
use App\Entity\Skill;
use App\Entity\User;
use App\Repository\UserActivityRepository;
use App\Repository\UserClubRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class LevelVoter extends Voter
{
    public const LEVEL_EDIT    = 'LEVEL_EDIT';
    public const LEVEL_DELETE  = 'LEVEL_DELETE';
    public const SKILL_CREATE  = 'SKILL_CREATE';
    public const SKILL_EDIT    = 'SKILL_EDIT';
    public const SKILL_DELETE  = 'SKILL_DELETE';

    public function __construct(
        private readonly UserClubRepository $userClubRepository,
        private readonly UserActivityRepository $userActivityRepository,
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (in_array($attribute, [self::LEVEL_EDIT, self::LEVEL_DELETE, self::SKILL_CREATE])) {
            return $subject instanceof Level;
        }

        if (in_array($attribute, [self::SKILL_EDIT, self::SKILL_DELETE])) {
            return $subject instanceof Skill;
        }

        return false;
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

        // Récupérer le Level selon le type du sujet
        $level = $subject instanceof Skill ? $subject->getLevel() : $subject;
        $club  = $level->getActivity()->getClub();

        $userClub = $this->userClubRepository->findOneBy([
            'member' => $user,
            'club'   => $club,
        ]);

        // Admin du club peut tout faire
        if ($userClub !== null && in_array('ADMIN', $userClub->getRoles())) {
            return true;
        }

        // Vérifier que l'utilisateur est TEACHER de l'activité
        $userActivity = $this->userActivityRepository->findOneBy([
            'member'   => $user,
            'activity' => $level->getActivity(),
        ]);
        $isTeacher = $userActivity !== null && $userActivity->getRole()->value === 'TEACHER';

        if (!$isTeacher) {
            return false;
        }

        return match ($attribute) {
            // SKILL_CREATE : tout teacher de l'activité peut créer (peu importe qui a créé le level)
            self::SKILL_CREATE => true,

            // LEVEL_EDIT / LEVEL_DELETE : teacher uniquement si c'est lui qui a créé le level
            self::LEVEL_EDIT,
            self::LEVEL_DELETE => $level->getCreatedBy()?->getId() === $user->getId(),

            // SKILL_EDIT / SKILL_DELETE : teacher uniquement si c'est lui qui a créé le skill
            self::SKILL_EDIT,
            self::SKILL_DELETE => $subject->getCreatedBy()?->getId() === $user->getId(),

            default => false,
        };
    }
}