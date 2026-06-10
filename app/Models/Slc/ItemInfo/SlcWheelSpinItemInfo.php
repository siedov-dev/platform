<?php

namespace App\Models\Slc\ItemInfo;

use App\Helpers\Device;
use App\Models\Slc\SlcItem;

class SlcWheelSpinItemInfo extends SlcBaseItemInfo implements ShopItemInfo
{
    private SlcItem $item;
    public function __construct(SlcItem $item)
    {
        $this->item = $item;
    }

    public function name(bool $with_amount = true): string
    {
        if (!empty($this->item->getName())) return $this->item->getName();

        return __('slc.wheel_item_name', ['amount' => $this->item->getAmount()]);
    }

    public function amount(): string
    {
        return '<span>x</span>'.$this->item->getAmount();
    }

    public function units(): string
    {
        return (__('slc.units_wager'));
    }

    public function description(): string
    {
        return view(Device::detect().'.slc.item-info.item-wheel-spin', ['item' => $this->getItem()]);
    }

    public function blockType(): string
    {
        return '';
    }

    public function renderShopImage(): string
    {
        $img = $this->item->getImage()?: "/images/icons/wheel.png";
        return "<img class='shop-item-img' src='$img'>";
    }

    public function getItem(): SlcItem
    {
        return $this->item;
    }

    public function renderItemIcon(): string
    {
        $img = $this->item->getImage()?: "/images/icons/wheel.png";
        return view('common.slc.item-icon', [
            'img' => $img,
            'amount' => $this->amount(),
            'units' => 'spins',
        ])->render();
    }
}
