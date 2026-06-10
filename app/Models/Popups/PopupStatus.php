<?php

namespace App\Models\Popups;

enum PopupStatus: int
{
    case New = 0;
    case Shown = 1;
    case Expired = 2;
    case Disabled = 3;
}
