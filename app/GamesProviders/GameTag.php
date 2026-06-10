<?php

namespace App\GamesProviders;

enum GameTag: string
{
    case New = 'new';
    case Crash = 'crash';
    case Top = 'top';
    case Hot = 'hot';
    case Jackpot = 'jackpot';
    case MegaWays = 'megaways';
    case FreeSpins = 'fs';
}
