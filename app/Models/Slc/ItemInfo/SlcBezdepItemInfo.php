<?php

namespace App\Models\Slc\ItemInfo;

use App\Helpers\Device;
use App\Models\Slc\BezdepItemConfig;
use App\Models\Slc\SlcItem;

class SlcBezdepItemInfo extends SlcBaseItemInfo implements ShopItemInfo
{
    private SlcItem $item;
    private BezdepItemConfig $params;
    public function __construct(SlcItem $item)
    {
        $this->item = $item;
        $this->params = BezdepItemConfig::first();
    }

    public function getItem(): SlcItem
    {
        return $this->item;
    }

    public function name(bool $with_amount = true): string
    {
        if (!empty($this->item->getName())) return $this->item->getName();
        $bonus_params = json_decode($this->params->getParams(), true);
        return __('slc.bezdep_bonus', [
            'fs' => $bonus_params['freespins']?? 0,
            'wager' => $bonus_params['wager']?? 0,
        ]);
    }

    public function amount(): string
    {
        $item_params = json_decode($this->item->getParams(), true);
        if (($item_params['freespins']?? 0) > 0) return $item_params['freespins'];

        $bonus_params = json_decode($this->params->getParams(), true);
        return $bonus_params['freespins'];
    }

    public function units(): string
    {
        return (__('slc.units_fs'));
    }

    public function description(): string
    {
        return view(Device::detect().'.slc.item-info.item-bezdep', ['item' => $this->getItem()]);
    }

    public function blockType(): string
    {
        return 'fs';
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
            'img' => '/img/inventory/bezdep.png',
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
            'img' => '/img/inventory/bezdep.png',
            'amount' => $this->amount(),
            'units' => $this->units(),
        ])->render();
    }

    public function daysActive(): int
    {
        return $this->params->getActiveDays();
    }

    public function canActivate(): bool
    {
        return true;
    }

}
