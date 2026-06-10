<?php

namespace App\GamesProviders\SoftSwiss\Models;

enum FsStatus: string
{
    case NotActivated = "not_activated";

    case Active = "active";
    case Expired = "expired";
    case Canceled = "canceled";
    case Played = "played";
}
