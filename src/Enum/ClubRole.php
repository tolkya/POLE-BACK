<?php

namespace App\Enum;

enum ClubRole: string
{
    case ADMIN     = 'ADMIN';
    case TEACHER   = 'TEACHER';
    case SECRETARY = 'SECRETARY';
    case MEMBER    = 'MEMBER';
    case USER      = 'USER';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}