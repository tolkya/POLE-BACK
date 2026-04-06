<?php

namespace App\Service;

use App\Entity\Activity;
use App\Entity\Club;
use App\Entity\NotificationEvent;
use App\Entity\NotificationReceipt;
use App\Entity\User;
use App\Enum\ActivityRole;
use App\Enum\NotificationType;
use App\Enum\UserActivityStatus;
use App\Repository\UserActivityRepository;
use App\Repository\UserClubRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

final class NotificationService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserRepository $userRepository,
        private readonly UserClubRepository $userClubRepository,
        private readonly UserActivityRepository $userActivityRepository,
    ) {}

    public function notifyClubCreated(Club $club, User $admin): void
    {
        $superAdmins = $this->userRepository->findSuperAdmins();
        if (empty($superAdmins)) {
            return;
        }

        $event = new NotificationEvent();
        $event->setNotifType(NotificationType::CLUB_CREATED->value);
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
    }

    public function notifyMemberValidated(Club $club, User $member): void
    {
        $event = new NotificationEvent();
        $event->setNotifType(NotificationType::MEMBER_VALIDATED->value);
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
    }

    public function notifyActivityJoinRequest(Activity $activity, User $requester): void
    {
        $club = $activity->getClub();

        // Requête ciblée : uniquement les ADMIN du club (plus de chargement de tous les membres)
        $adminUserClubs = $this->userClubRepository->findAdminsByClub($club);

        // Requête ciblée : uniquement les TEACHER APPROVED de cette activité
        $teacherUserActivities = $this->userActivityRepository->findBy([
            'activity' => $activity,
            'role'     => ActivityRole::TEACHER,
            'status'   => UserActivityStatus::APPROVED,
        ]);

        $recipients = [];
        foreach ($adminUserClubs as $uc) {
            $recipients[$uc->getMember()->getId()] = $uc->getMember();
        }
        foreach ($teacherUserActivities as $ua) {
            if ($ua->getMember() !== $requester) {
                $recipients[$ua->getMember()->getId()] = $ua->getMember();
            }
        }

        if (empty($recipients)) {
            return;
        }

        $event = new NotificationEvent();
        $event->setNotifType(NotificationType::ACTIVITY_JOIN_REQUEST->value);
        $event->setSubjectType('Activity');
        $event->setSubjectId($activity->getId());
        $event->setTriggeredBy($requester);
        $event->setContext([
            'activityName'       => $activity->getName(),
            'requesterEmail'     => $requester->getEmail(),
            'requesterFirstName' => $requester->getFirstName(),
            'requesterLastName'  => $requester->getLastName(),
        ]);
        $this->em->persist($event);

        foreach ($recipients as $recipient) {
            $receipt = new NotificationReceipt();
            $receipt->setEvent($event);
            $receipt->setRecipient($recipient);
            $this->em->persist($receipt);
        }
    }

    public function notifyActivityJoinApproved(Activity $activity, User $member): void
    {
        $event = new NotificationEvent();
        $event->setNotifType(NotificationType::ACTIVITY_JOIN_APPROVED->value);
        $event->setSubjectType('Activity');
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
    }

    public function notifyMemberJoinRequest(Club $club, User $member): void
    {
        $admins = $this->userClubRepository->findAdminsByClub($club);
        if (empty($admins)) {
            return;
        }

        $event = new NotificationEvent();
        $event->setNotifType(NotificationType::MEMBER_JOIN_REQUEST->value);
        $event->setSubjectType('Club');
        $event->setSubjectId($club->getId());
        $event->setTriggeredBy($member);
        $event->setContext([
            'clubName'        => $club->getName(),
            'memberEmail'     => $member->getEmail(),
            'memberFirstName' => $member->getFirstName(),
            'memberLastName'  => $member->getLastName(),
        ]);
        $this->em->persist($event);

        foreach ($admins as $uc) {
            $receipt = new NotificationReceipt();
            $receipt->setEvent($event);
            $receipt->setRecipient($uc->getMember());
            $this->em->persist($receipt);
        }
    }

    public function notifyMemberJoinApproved(Club $club, User $member): void
    {
        $event = new NotificationEvent();
        $event->setNotifType(NotificationType::MEMBER_JOIN_APPROVED->value);
        $event->setSubjectType('Club');
        $event->setSubjectId($club->getId());
        $event->setContext([
            'clubName' => $club->getName(),
        ]);
        $this->em->persist($event);

        $receipt = new NotificationReceipt();
        $receipt->setEvent($event);
        $receipt->setRecipient($member);
        $this->em->persist($receipt);
    }

    public function notifyActivityJoinRejected(Activity $activity, User $member): void
    {
        $event = new NotificationEvent();
        $event->setNotifType(NotificationType::ACTIVITY_JOIN_REJECTED->value);
        $event->setSubjectType('Activity');
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
    }
}