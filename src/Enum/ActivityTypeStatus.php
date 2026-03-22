<?php

namespace App\Enum;

enum ActivityTypeStatus: string
{
    case ACTIVE  = 'ACTIVE';
    case BLOCKED = 'BLOCKED';
}