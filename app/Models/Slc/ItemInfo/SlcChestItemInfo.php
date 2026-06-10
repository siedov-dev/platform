<?php

namespace App\Models\Slc\ItemInfo;

use App\Helpers\Device;
use App\Models\Slc\SlcItem;

class SlcChestItemInfo extends SlcBaseItemInfo implements ShopItemInfo
{
    private SlcItem $item;
    public function __construct(SlcItem $item)
    {
        $this->item = $item;
    }

    public function name(bool $with_amount = true): string
    {
        if (!empty($this->item->getName())) return $this->item->getName();

        return __('chests.'. $this->getItem()->getType()->name);
    }

    public function amount(): string
    {
        return "";
    }

    public function units(): string
    {
        return "";
    }

    public function description(): string
    {
        return view(Device::detect().'.slc.item-info.item-chest', ['item' => $this->getItem()]);
    }

    public function blockType(): string
    {
        return '';
    }

    public function getItem(): SlcItem
    {
        return $this->item;
    }

    public function renderShopImage(): string
    {
        $img = $this->item->getImage()?: "/img/chests/". $this->getItem()->getType()->name .".png";
        return "<img class='shop-item-img' src='$img'>";
    }

    public function renderItemIcon(): string
    {
        $img = $this->item->getImage()?: "/img/chests/". $this->getItem()->getType()->name .".png";
        return view('common.slc.item-icon', [
            'img' => $img,
            'amount' => $this->amount(),
            'units' => $this->units(),
        ])->render();
    }
}
