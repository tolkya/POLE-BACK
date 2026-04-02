<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\ClubRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class ClubSearchProvider implements ProviderInterface
{
    public function __construct(
        private readonly ClubRepository $clubRepository,
        private readonly RequestStack $requestStack,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $name = $this->requestStack->getCurrentRequest()?->query->get('name', '');

        if (strlen(trim($name)) < 2) {
            return [];
        }

        return $this->clubRepository->searchByName(trim($name));
    }
}
