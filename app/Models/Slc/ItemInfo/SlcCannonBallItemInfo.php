<?php

namespace App\Models\Slc\ItemInfo;

use App\Helpers\Device;
use App\Models\Slc\SlcItem;
use App\Models\Slc\SlcUserShopItem;

class SlcCannonBallItemInfo extends SlcBaseItemInfo implements ShopItemInfo
{
    private SlcItem $item;
    public function __construct(SlcItem $item)
    {
        $this->item = $item;
    }

    public function name(bool $with_amount = true): string
    {
        if (!empty($this->item->getName())) return $this->item->getName();

        return __('cashback-plus.'. $this->getItem()->getType()->name).
            ($this->getItem()->getAmount() > 1? ($with_amount? ' X'.$this->getItem()->getAmount(): '') : '');
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
        return view(Device::detect().'.slc.item-info.item-'.$this->getItem()->getType()->name, ['item' => $this->getItem()]);
    }

    public function getItem(): SlcItem
    {
        return $this->item;
    }

    public function renderShopImage(): string
    {
        $img = $this->item->getImage()?: "/img/cashback/". $this->getItem()->getType()->name .".svg";
        return "<img class='shop-item-img' src='$img'>";
    }

    public function renderItemIcon(): string
    {
        $img = $this->item->getImage()?: "/img/cashback/". $this->getItem()->getType()->name .".svg";
        return view('common.slc.item-icon', [
            'img' => $img,
            'amount' => $this->amount(),
            'units' => $this->units(),
        ])->render();
    }

    public function isStackable(): bool
    {
        return false;
    }

    public function isGroupable(): bool
    {
        return true;
    }

    public function renderInventoryItem(SlcUserShopItem $item): string
    {
        return view('common.slc.inventory-items.cannonball-item', [
            'info' => $this,
            'item' => $item,
        ])->render();
    }

    public function daysActive(): int
    {
        return 7;
    }
}
