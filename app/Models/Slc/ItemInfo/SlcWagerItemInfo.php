<?php

namespace App\Models\Slc\ItemInfo;

use App\Helpers\Device;
use App\Models\Slc\SlcItem;

class SlcWagerItemInfo extends SlcBaseItemInfo implements ShopItemInfo
{
    private SlcItem $item;
    public function __construct(SlcItem $item)
    {
        $this->item = $item;
    }

    public function name(bool $with_amount = true): string
    {
        if (!empty($this->item->getName())) return $this->item->getName();

        return __('slc.wager_item_name', ['amount' => $this->item->getAmount()]);
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
        return view(Device::detect().'.slc.item-info.item-wager', ['item' => $this->getItem()]);
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
        if (!empty($img = $this->item->getImage())) {
            return view('common.slc.item-user-icon', [
                'img' => $img,
                'amount' => '',
                'units' => '',
            ])->render();
        }

        return view('common.slc.bonus-item-icon', [
            'img' => '/img/inventory/wager.png',
            'amount' => $this->amount(),
            'units' => $this->units(),
        ])->render();
    }

    public function renderItemIcon(): string
    {
        if (!empty($img = $this->item->getImage())) {
            return view('common.slc.item-user-icon', [
                'img' => $img,
                'amount' => $this->amount(),
                'units' => $this->units(),
            ])->render();
        }

        return view('common.slc.item-icon', [
            'img' => '/img/inventory/wager.png',
            'amount' => $this->amount(),
            'units' => $this->units(),
        ])->render();
    }
}
