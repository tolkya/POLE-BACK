<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\State\Pagination\ArrayPaginator;
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

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $clubId = $uriVariables['clubId'] ?? null;

        $club = $this->clubRepository->find($clubId);
        if (!$club) {
            throw new NotFoundHttpException('Club not found');
        }

        if (!$this->security->isGranted('CLUB_ADMIN', $club)) {
            throw new AccessDeniedHttpException('You are not admin of this club');
        }

        $request = $this->requestStack->getCurrentRequest();
        $page    = max(1, (int) ($request?->query->get('page', 1)));
        $limit   = 20;

        $filters = array_filter([
            'role'   => $request?->query->get('role'),
            'search' => $request?->query->get('search'),
            'page'   => $page,
            'limit'  => $limit,
        ]);

        $total   = $this->userClubRepository->countByClub($club, $filters);
        $members = $this->userClubRepository->findByClub($club, $filters);

        return new ArrayPaginator($members, ($page - 1) * $limit, $total);
    }
}