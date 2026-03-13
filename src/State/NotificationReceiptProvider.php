<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\NotificationReceiptRepository;
use Symfony\Bundle\SecurityBundle\Security;

class NotificationReceiptProvider implements ProviderInterface
{
    public function __construct(
        private NotificationReceiptRepository $repository,
        private Security $security,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        return $this->repository->findBy(
            ['recipient' => $this->security->getUser()],
            ['createdAt' => 'DESC']
        );
    }
}