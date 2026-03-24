<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\LevelRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class SkillsProvider implements ProviderInterface
{
    public function __construct(
        private readonly LevelRepository $levelRepository,
        private readonly Security $security,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $level = $this->levelRepository->find($uriVariables['levelId']);
        if ($level === null) {
            throw new NotFoundHttpException('Niveau introuvable.');
        }

        if (!$this->security->isGranted('CLUB_VIEW', $level->getActivity()->getClub())) {
            throw new AccessDeniedHttpException('Vous n\'êtes pas membre de ce club.');
        }

        return $level->getSkills()->toArray();
    }
}