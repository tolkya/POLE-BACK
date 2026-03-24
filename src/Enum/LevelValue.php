<?php

namespace App\Enum;

enum LevelValue: string
{
    case NOVICE        = 'NOVICE';
    case INITIATION    = 'INITIATION';
    case DEBUTANT      = 'DEBUTANT';
    case INTERMEDIAIRE = 'INTERMEDIAIRE';
    case CONFIRME      = 'CONFIRME';
    case AVANCE        = 'AVANCE';
    case MASTER        = 'MASTER';
}