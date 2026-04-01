<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\User;
use App\Repository\UserActivityRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class MyActivitiesProvider implements ProviderInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly UserActivityRepository $userActivityRepository,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new AccessDeniedHttpException('Vous devez être connecté.');
        }

        $clubId = isset($context['filters']['clubId'])
            ? (int) $context['filters']['clubId']
            : null;

        return $this->userActivityRepository->findByMemberAndClub($user, $clubId);
    }
}