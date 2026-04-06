<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\UserClub;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use App\Security\Voter\UserClubVoter;
use App\Service\NotificationService;

class UserClubPatch implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Security $security,
        private readonly NotificationService $notificationService,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): UserClub
    {
        /** @var UserClub $data */
        if (!$this->security->isGranted(UserClubVoter::EDIT, $data)) {
            throw new AccessDeniedHttpException('Accès refusé.');
        }

        // Détecter si c'est une validation de membre (validatedAt vient d'être posé)
        $previousValidatedAt = $context['previous_data']?->getValidatedAt();
        if ($previousValidatedAt === null && $data->getValidatedAt() !== null) {
            $this->notificationService->notifyMemberJoinApproved(
                $data->getClub(),
                $data->getMember()
            );
        }

        $this->em->flush();

        return $data;
    }
}