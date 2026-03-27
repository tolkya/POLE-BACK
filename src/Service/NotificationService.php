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
        private readonly \App\Repository\UserClubRepository $userClubRepository,
        private readonly \App\Repository\UserActivityRepository $userActivityRepository,
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
    
    public function notifyMemberValidated(Club $club, User $member): void
    {
        $event = new NotificationEvent();
        $event->setNotifType('MEMBER_VALIDATED');
        $event->setSubjectType('Club');
        $event->setSubjectId($club->getId());
        $event->setTriggeredBy($member);
        $event->setContext([
            'clubName' => $club->getName(),
            'clubCode' => $club->getClubCode(),
        ]);
        $this->em->persist($event);

        $receipt = new NotificationReceipt();
        $receipt->setEvent($event);
        $receipt->setRecipient($member);
        $this->em->persist($receipt);
        $this->em->flush();
    }

    public function notifyActivityJoinRequest(\App\Entity\Activity $activity, User $requester): void
    {
        $club = $activity->getClub();

        // Récupérer les admins du club
        $admins = array_filter(
            $this->userClubRepository->findByClub($club),
            fn($uc) => in_array('ADMIN', $uc->getRoles())
        );

        // Récupérer les profs de l'activité (APPROVED uniquement)
        $teachers = array_filter(
            $this->userActivityRepository->findBy(['activity' => $activity, 'role' => \App\Enum\ActivityRole::TEACHER, 'status' => \App\Enum\UserActivityStatus::APPROVED]),
            fn($ua) => $ua->getMember() !== $requester
        );

        $recipients = [];
        foreach ($admins as $uc) {
            $recipients[$uc->getMember()->getId()] = $uc->getMember();
        }
        foreach ($teachers as $ua) {
            $recipients[$ua->getMember()->getId()] = $ua->getMember();
        }

        if (empty($recipients)) {
            return;
        }

        $event = new NotificationEvent();
        $event->setNotifType('ACTIVITY_JOIN_REQUEST');
        $event->setSubjectType('UserActivity');
        $event->setSubjectId($activity->getId());
        $event->setTriggeredBy($requester);
        $event->setContext([
            'activityName'      => $activity->getName(),
            'requesterEmail'    => $requester->getEmail(),
            'requesterFirstName'=> $requester->getFirstName(),
            'requesterLastName' => $requester->getLastName(),
        ]);
        $this->em->persist($event);

        foreach ($recipients as $recipient) {
            $receipt = new NotificationReceipt();
            $receipt->setEvent($event);
            $receipt->setRecipient($recipient);
            $this->em->persist($receipt);
        }
        $this->em->flush();
    }

    public function notifyActivityJoinApproved(\App\Entity\Activity $activity, User $member): void
    {
        $event = new NotificationEvent();
        $event->setNotifType('ACTIVITY_JOIN_APPROVED');
        $event->setSubjectType('UserActivity');
        $event->setSubjectId($activity->getId());
        $event->setContext([
            'activityName' => $activity->getName(),
            'clubName'     => $activity->getClub()->getName(),
        ]);
        $this->em->persist($event);

        $receipt = new NotificationReceipt();
        $receipt->setEvent($event);
        $receipt->setRecipient($member);
        $this->em->persist($receipt);
        $this->em->flush();
    }
}