<?php

namespace App\Models\Slc\ItemInfo;

use App\Models\Slc\SlcItem;
use App\Models\Slc\SlcShopItem;

class SlcUnknownItemInfo extends SlcBaseItemInfo implements ShopItemInfo
{
    public function __construct(SlcItem $item)
    {
    }

    public function name(bool $with_amount = true): string
    {
        return "Unknown";
    }

    public function amount(): string
    {
        return "Unknown";
    }

    public function units(): string
    {
        return "Unknown";
    }

    public function description(): string
    {
        return "unknown";
    }

    public function blockType(): string
    {
        return 'fs';
    }

    public function getItem(): SlcItem
    {
        return new SlcShopItem();
    }

    public function renderShopImage(): string
    {
        return view('common.slc.shop-item-circle', ['info' => $this])->render();
    }

    public function renderItemIcon(): string
    {
        return view('common.slc.item-icon', [
            'img' => '/images/svg/shop-block-circle-'.$this->blockType().'.svg',
            'amount' => $this->amount(),
            'units' => $this->units(),
        ])->render();
    }
}
