<?php

namespace App\Models\Slc\ItemInfo;

use App\Models\Slc\SlcUserShopItem;

abstract class SlcBaseItemInfo implements ShopItemInfo
{

    public function getShortDescription(): string
    {
        return __('slc.short_descr-'.$this->getItem()->getType()->name);
    }

    public function amount(): string
    {
        return "";
    }

    public function units(): string
    {
        return "";
    }

    public function blockType(): string
    {
        return '';
    }

    public function isStackable(): bool
    {
        return false;
    }
    public function isGroupable(): bool
    {
        return false;
    }

    public function isExpirable(): bool
    {
        return !$this->isStackable();
    }

    public function renderInventoryItem(SlcUserShopItem $item): string
    {
        return view('common.slc.inventory-items.default-item', [
            'info' => $this,
            'item' => $item,
        ])->render();
    }

    public function renderRangeItemIcon($from, $to): string
    {
        return $this->renderItemIcon();
    }

    public function daysActive(): int
    {
        return 3;
    }

    public function canActivate(): bool
    {
        return true;
    }

    public function getActionName(): string
    {
        return __('slc.activate');
    }
}
