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
        $newStatus      = $data->getStatus();

        // Pas de changement → rien à faire
        if ($previousStatus === $newStatus) {
            return $data;
        }

        // Transitions autorisées
        $allowed = match($previousStatus) {
            UserActivityStatus::PENDING  => [UserActivityStatus::APPROVED, UserActivityStatus::REJECTED],
            UserActivityStatus::APPROVED => [UserActivityStatus::LEFT],
            UserActivityStatus::REJECTED => [UserActivityStatus::PENDING, UserActivityStatus::APPROVED],
            UserActivityStatus::LEFT     => [UserActivityStatus::PENDING],
            default => [],
        };

        if (!in_array($newStatus, $allowed, true)) {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException(
                sprintf('Transition %s → %s non autorisée.', $previousStatus->value, $newStatus->value)
            );
        }

        // Notifications
        if ($newStatus === UserActivityStatus::APPROVED && $previousStatus !== UserActivityStatus::APPROVED) {
            $this->notificationService->notifyActivityJoinApproved(
                $data->getActivity(),
                $data->getMember()
            );
        } elseif ($newStatus === UserActivityStatus::REJECTED && $previousStatus !== UserActivityStatus::REJECTED) {
            $this->notificationService->notifyActivityJoinRejected(
                $data->getActivity(),
                $data->getMember()
            );
        }

        $this->em->flush();

        return $data;
    }
}