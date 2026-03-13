<?php

namespace App\Service;

use App\Entity\Club;
use App\Entity\NotificationEvent;
use App\Entity\NotificationReceipt;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

final class NotificationService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserRepository $userRepository,
    ) {}

    public function notifyClubCreated(Club $club, User $admin): void
    {
        $superAdmins = $this->userRepository->findSuperAdmins();
        if (empty($superAdmins)) {
            return;
        }

        $event = new NotificationEvent();
        $event->setNotifType('CLUB_CREATED');
        $event->setSubjectType('Club');
        $event->setSubjectId($club->getId());
        $event->setTriggeredBy($admin);
        $event->setContext([
            'clubName'       => $club->getName(),
            'clubCode'       => $club->getClubCode(),
            'adminEmail'     => $admin->getEmail(),
            'adminFirstName' => $admin->getFirstName(),
            'adminLastName'  => $admin->getLastName(),
        ]);
        $this->em->persist($event);

        foreach ($superAdmins as $superAdmin) {
            $receipt = new NotificationReceipt();
            $receipt->setEvent($event);
            $receipt->setRecipient($superAdmin);
            $this->em->persist($receipt);
        }

        $this->em->flush();
    }
}