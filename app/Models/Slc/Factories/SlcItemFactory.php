<?php

namespace App\Models\Slc\Factories;

use App\Models\Slc\Activators\SlcItemActivator;
use App\Models\Slc\ItemInfo\ShopItemInfo;
use App\Models\Slc\SlcItem;
use App\Models\Slc\SlcUserShopItem;
use App\MUser;

interface SlcItemFactory
{
    public function getItemInfo(SlcItem $item, MUser $user = null): ShopItemInfo;

    public function getItemActivator(SlcItem $item): SlcItemActivator;

}
