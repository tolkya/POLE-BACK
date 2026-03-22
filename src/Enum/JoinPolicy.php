<?php

namespace App\Enum;

enum JoinPolicy: string
{
    case AUTO_ACCEPT       = 'AUTO_ACCEPT';
    case MANUAL_VALIDATION = 'MANUAL_VALIDATION';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}