<?php

namespace App\Service;

use App\Entity\Club;
use App\Entity\Notification;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service centralisé pour créer des notifications en BDD.
 *
 * Ce service est "simulé" dans le sens où les notifications sont créées en BDD
 * mais ne déclenchent pas encore d'envoi d'email (ça viendra plus tard avec Symfony Mailer).
 * Les Super Admins les liront depuis leur dashboard.
 */
final class NotificationService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserRepository $userRepository,
    ) {}

    /**
     * Crée une notification pour chaque Super Admin quand un club est créé.
     *
     * Appelé après la création du User + Club en BDD.
     * Si aucun Super Admin n'existe encore (fresh install), pas d'erreur — on ignore silencieusement.
     */
    public function notifyClubCreated(Club $club, User $admin): void
    {
        $superAdmins = $this->userRepository->findSuperAdmins();

        if (empty($superAdmins)) {
            // Pas de Super Admin enregistré — notification ignorée silencieusement
            return;
        }

        foreach ($superAdmins as $superAdmin) {
            $notification = new Notification();
            $notification->setRecipient($superAdmin);
            $notification->setNotifType('CLUB_CREATED');
            $notification->setMessage(sprintf(
                'Nouvelle demande de création de club "%s" par %s %s (%s).',
                $club->getName(),
                $admin->getFirstName(),
                $admin->getLastName(),
                $admin->getEmail()
            ));
            // subjectType/subjectId permettent de retrouver l'entité liée depuis le dashboard
            $notification->setSubjectType('Club');
            $notification->setSubjectId($club->getId());
            $notification->setContext([
                'clubCode' => $club->getClubCode(),
                'adminEmail' => $admin->getEmail(),
            ]);

            $this->em->persist($notification);
        }

        $this->em->flush();
    }
}
