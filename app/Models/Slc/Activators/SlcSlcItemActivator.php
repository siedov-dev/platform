<?php

namespace App\Models\Slc\Activators;

use App\Helpers\Hlog;
use App\Helpers\RdRand;
use App\Models\Bonuses\UserBonus;
use App\Models\Slc\Chest\ChestType;
use App\Models\Slc\Chest\SlcChestItem;
use App\Models\Slc\SlcItemType;
use App\Models\Slc\SlcUserInventory;
use App\Models\Slc\SlcUserItemStatus;
use App\Models\Slc\SlcUserShopItem;
use App\MUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SlcSlcItemActivator implements SlcItemActivator
{

    public function activateItem(SlcUserShopItem $item, array $params = [])
    {
        $user = MUser::findOrFail($item->getUserId());
        $inventory = SlcUserInventory::forUser($user);
        $inventory->addItem($item);
        $item->setAmount(0);
        $item->save();
        $item->checkStatus();
    }

    public function requiresDialog(SlcUserShopItem $item): bool
    {
        return false;
    }

    public function getDialog(SlcUserShopItem $item): string
    {
        return "";
    }
}
