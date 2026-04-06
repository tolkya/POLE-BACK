<?php

namespace App\ApiResource;

use Symfony\Component\Serializer\Attribute\Groups;

final class ClubAdminStats
{
    public function __construct(
        #[Groups(['club:admin_stats'])]
        public readonly int $membersCount,
        #[Groups(['club:admin_stats'])]
        public readonly int $teachersCount,
        #[Groups(['club:admin_stats'])]
        public readonly int $activitiesCount,
        /** Inscriptions activités en attente de validation (UserActivity PENDING) */
        #[Groups(['club:admin_stats'])]
        public readonly int $pendingEnrollments,
        /** Membres en attente de validation manuelle (UserClub sans validatedAt) */
        #[Groups(['club:admin_stats'])]
        public readonly int $pendingMembers,
        /** Notifications non lues pour l'admin connecté */
        #[Groups(['club:admin_stats'])]
        public readonly int $unreadNotifications,
    ) {}
}
