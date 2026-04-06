<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\ClubRepository;
use App\Repository\UserClubRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ClubMembersProvider implements ProviderInterface
{
    public function __construct(
        private readonly ClubRepository $clubRepository,
        private readonly UserClubRepository $userClubRepository,
        private readonly Security $security,
        private readonly RequestStack $requestStack,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $clubId = $uriVariables['clubId'] ?? null;
        
        $club = $this->clubRepository->find($clubId);
        if (!$club) {
            throw new NotFoundHttpException('Club not found');
        }

        // Vérifie que l'utilisateur est admin de ce club
        if (!$this->security->isGranted('CLUB_ADMIN', $club)) {
            throw new AccessDeniedHttpException('You are not admin of this club');
        }

        // Lecture des filtres passés en query params (?search=jean&role=TEACHER)
        $request = $this->requestStack->getCurrentRequest();
        $role    = $request?->query->get('role');
        $search  = $request?->query->get('search');
        return $this->userClubRepository->findByClub($club, array_filter(compact('role', 'search')));
    }
}
