<?php

namespace App\Enum;

enum ActivityStatus: string
{
    case ACTIVE    = 'ACTIVE';
    case SUSPENDED = 'SUSPENDED';
}