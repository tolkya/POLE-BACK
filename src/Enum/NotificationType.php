<?php

namespace App\Enum;

enum NotificationType: string
{
    // Lifecycle club
    case CLUB_CREATED          = 'CLUB_CREATED';
    case CLUB_VALIDATED        = 'CLUB_VALIDATED';
    case CLUB_REJECTED         = 'CLUB_REJECTED';

    // Membership club
    case MEMBER_VALIDATED      = 'MEMBER_VALIDATED';
    case MEMBER_JOIN_REQUEST   = 'MEMBER_JOIN_REQUEST';
    case MEMBER_JOIN_APPROVED  = 'MEMBER_JOIN_APPROVED';
    case MEMBER_EXCLUDED       = 'MEMBER_EXCLUDED';

    // Inscriptions activité
    case ACTIVITY_JOIN_REQUEST  = 'ACTIVITY_JOIN_REQUEST';
    case ACTIVITY_JOIN_APPROVED = 'ACTIVITY_JOIN_APPROVED';
    case ACTIVITY_JOIN_REJECTED = 'ACTIVITY_JOIN_REJECTED';
}