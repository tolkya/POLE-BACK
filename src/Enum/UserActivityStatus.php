<?php

namespace App\Enum;

enum UserActivityStatus: string
{
    case PENDING  = 'PENDING';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';
    case LEFT    = 'LEFT';
}