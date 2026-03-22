<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\ClubMembership;
use App\Entity\UserClub;
use App\Repository\ClubRepository;
use App\Repository\UserClubRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ClubMembershipProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ClubRepository $clubRepository,
        private readonly UserClubRepository $userClubRepository,
        private readonly NotificationService $notificationService,
        private readonly Security $security,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ClubMembership
    {
        $user = $this->security->getUser();

        $club = $this->clubRepository->findByClubCode($data->clubCode);
        if ($club === null) {
            throw new NotFoundHttpException('Code club invalide.');
        }

        // Vérifier si l'utilisateur est déjà membre
        $existing = $this->userClubRepository->findOneBy([
            'member' => $user,
            'club'   => $club,
        ]);
        if ($existing !== null) {
            throw new ConflictHttpException('Vous êtes déjà membre de ce club.');
        }

        $userClub = new UserClub();
        $userClub->setMember($user);
        $userClub->setClub($club);
        $userClub->setRoles(['MEMBER']);
        $userClub->setValidatedAt(new \DateTimeImmutable());
/*         $isAutoAccepted = $club->getJoinPolicy() === JoinPolicy::AUTO_ACCEPT->value;
        if ($isAutoAccepted) {
            $userClub->setValidatedAt(new \DateTimeImmutable());
        } */

        $this->em->persist($userClub);
        $this->em->flush();

        // Notifications
        $this->notificationService->notifyMemberValidated($club, $user);
        $this->em->flush();

        $data->message = 'Vous avez bien rejoint le club ' . $club->getName() . '.';
        $data->userClubId = $userClub->getId();

        return $data;
    }
}