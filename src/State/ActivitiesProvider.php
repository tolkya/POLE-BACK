<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\ActivityRepository;
use App\Repository\ClubRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ActivitiesProvider implements ProviderInterface
{
    public function __construct(
        private readonly ClubRepository $clubRepository,
        private readonly ActivityRepository $activityRepository,
        private readonly Security $security,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $club = $this->clubRepository->find($uriVariables['clubId']);
        if ($club === null) {
            throw new NotFoundHttpException('Club introuvable.');
        }

        // Membre OU admin du club peut voir les activités
        if (!$this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedHttpException('Vous devez être connecté pour voir les activités.');
        }

        return $this->activityRepository->findBy(['club' => $club]);
    }
}