<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\UserActivity;
use App\Enum\UserActivityStatus;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;

final class UserActivityStatusProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly NotificationService $notificationService,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): UserActivity
    {
        /** @var UserActivity $data */
        $previousStatus = $context['previous_data']?->getStatus();

        $this->em->flush();

        // Notifier le membre uniquement si le status vient de passer à APPROVED
        if (
            $data->getStatus() === UserActivityStatus::APPROVED
            && $previousStatus !== UserActivityStatus::APPROVED
        ) {
            $this->notificationService->notifyActivityJoinApproved(
                $data->getActivity(),
                $data->getMember()
            );
        }

        return $data;
    }
}