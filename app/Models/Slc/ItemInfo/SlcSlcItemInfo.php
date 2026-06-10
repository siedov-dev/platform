<?php

namespace App\Models\Slc\ItemInfo;

use App\Helpers\Device;
use App\Models\Slc\SlcItem;

class SlcSlcItemInfo extends SlcBaseItemInfo implements ShopItemInfo
{
    private SlcItem $item;
    public function __construct(SlcItem $item)
    {
        $this->item = $item;
    }

    public function name(bool $with_amount = true): string
    {
        return 'SLC '.$this->getItem()->getAmount();
    }

    public function amount(): string
    {
        return ''.$this->getItem()->getAmount();
    }

    public function units(): string
    {
        return "";
    }

    public function description(): string
    {
        return view(Device::detect().'.slc.item-info.item-'.$this->getItem()->getType()->value, ['item' => $this->getItem()]);
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
        return "<img class='shop-item-img' src='/images/svg/slc-icon.svg'>";
    }

    public function renderItemIcon(): string
    {
        return view('common.slc.slc-item-icon', [
            'img' => "/images/svg/balans-icon.svg",
            'amount' => $this->amount(),
            'units' => $this->units(),
        ])->render();
    }

    public function renderRangeItemIcon($from, $to): string
    {
        if ($to <= $from) return parent::renderRangeItemIcon($from, $to);

        return view('common.slc.slc-item-icon', [
            'img' => "/images/svg/slc-icon.svg",
            'amount' => 'x'.$from,
            'bottom_amount' => 'x'.$to,
            'units' => '',
        ])->render();
    }

    public function isStackable(): bool
    {
        return true;
    }
}
