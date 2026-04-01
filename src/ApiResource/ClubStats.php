<?php

namespace App\ApiResource;

use Symfony\Component\Serializer\Attribute\Groups;

final class ClubStats
{
    public function __construct(
        #[Groups(['club:stats'])]
        public readonly int $membersCount,
        #[Groups(['club:stats'])]
        public readonly int $activitiesCount,
        #[Groups(['club:stats'])]
        public readonly int $teachersCount,
    ) {}
}