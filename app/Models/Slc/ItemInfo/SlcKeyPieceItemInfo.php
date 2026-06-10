<?php

namespace App\Models\Slc\ItemInfo;

use App\Helpers\Device;
use App\Models\Slc\SlcConfig;
use App\Models\Slc\SlcItem;
use App\Models\Slc\SlcUserShopItem;

class SlcKeyPieceItemInfo extends SlcBaseItemInfo implements ShopItemInfo
{
    private SlcItem $item;
    public function __construct(SlcItem $item)
    {
        $this->item = $item;
    }

    public function name(bool $with_amount = true): string
    {
        if (!empty($this->item->getName())) return $this->item->getName();

        return __('chests.'. $this->getItem()->getType()->name). ($with_amount? ' X'.$this->getItem()->getAmount(): '');
    }

    public function getShortDescription(): string
    {
        return __('slc.short_descr-'.$this->getItem()->getType()->name, [
            'target' => SlcConfig::getParams()->getKeyPiecesRequired($this->getItem()->getType()),
        ]);
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
        $img = $this->item->getImage()?: "/img/chests/".$this->getItem()->getType()->name.".svg";
        return "<img class='shop-item-img' src='$img'>";
    }

    public function renderItemIcon(): string
    {
        $img = $this->item->getImage()?: "/img/chests/".$this->getItem()->getType()->name.".svg";
        return view('common.slc.item-icon', [
            'img' => $img,
            'amount' => $this->amount(),
            'units' => $this->units(),
        ])->render();
    }

    public function isStackable(): bool
    {
        return true;
    }

    public function isExpirable(): bool
    {
        return false;
    }

    public function renderInventoryItem(SlcUserShopItem $item): string
    {
        return view('common.slc.inventory-items.key-piece-item', [
            'info' => $this,
            'item' => $item,
        ])->render();
    }

    public function getActionName(): string
    {
        return __('slc.collect');
    }
}
