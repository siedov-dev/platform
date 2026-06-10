<?php

namespace App\Models\Slc\Activators;

use App\Helpers\Hlog;
use App\Helpers\RdRand;
use App\Models\Bonuses\UserBonus;
use App\Models\Slc\Chest\ChestType;
use App\Models\Slc\Chest\SlcChestItem;
use App\Models\Slc\SlcItemType;
use App\Models\Slc\SlcUserItemStatus;
use App\Models\Slc\SlcUserShopItem;
use App\MUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SlcCannonballItemActivator implements SlcItemActivator
{

    public function activateItem(SlcUserShopItem $item, array $params = [])
    {
    }

    public function requiresDialog(SlcUserShopItem $item): bool
    {
        return false;
    }

    public function getDialog(SlcUserShopItem $item): string
    {
        return '';
    }
}
