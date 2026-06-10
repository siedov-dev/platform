<?php

namespace App\Models\Slc\ItemInfo;

use App\Models\Bonuses\KUserBonus;
use App\Models\Slc\SlcItem;
use App\MUser;
use App\Wallet;
use Illuminate\Support\Facades\Auth;

class SlcBonusItemInfo extends SlcBaseItemInfo implements ShopItemInfo
{
    private SlcItem $item;
    private ?KUserBonus $bonus = null;

    public function __construct(SlcItem $item, ?MUser $user = null)
    {
        $this->item = $item;
        //if ($user !== null || Auth::check())
            $this->bonus = KUserBonus::fromParams($item->getBonusType(), $item->getParams(), $user?->getId()?? Auth::id() ?? 0);
    }

    public function getItem(): SlcItem
    {
        return $this->item;
    }

    public function name(bool $with_amount = true): string
    {
        if (!empty($this->item->getName())) return $this->item->getName();
        if ($this->bonus === null) return "Bonus";

        if ($this->bonus?->isFreeSpins()) {
            if ($this->bonus->isNonDeposit()) {
                return __('slc.non_dep_fs_bonus', [
                    'fs' => $this->bonus->spins_total,
                ]);
            }
            return __('slc.fs_wager_bonus', [
                'fs' => $this->bonus->spins_total,
                'wager' => $this->bonus->getJsonParam('wager', '?')
            ]);
        }
        if ($this->bonus?->isNonDeposit()) {
            return __('slc.non_dep_bonus', [
                'money' => $this->bonus->money_available,
                'curr' => Wallet::currencySymbol($this->bonus->getCurrency()),
            ]);
        }
        if ($this->bonus?->isDeposit()) {
            return __('slc.deposit_bonus', [
                'amount' => $this->bonus->getJsonParam('multiplier'),
                'wager' => $this->bonus->getJsonParam('wager'),
            ]);
        }
        return $this->bonus?->getName();
    }

    public function amount(): string
    {
        if ($this->bonus?->isFreeSpins()) return $this->bonus->spins_total;
        if ($this->bonus?->isNonDeposit()) return $this->bonus->money_available . '<span>' . Wallet::currencySymbol($this->bonus->getCurrency()) . '</span>';
        if ($this->bonus?->isDeposit()) return $this->bonus->getJsonParam('multiplier') . '<span>%</span>';
        return '';
    }

    public function units(): string
    {
        if ($this->bonus?->isFreeSpins()) return (__('slc.units_fs'));
        if ($this->bonus?->isNonDeposit()) return (__('slc.units_non_dep'));
        if ($this->bonus?->isDeposit()) return (__('slc.units_deposit'));
        return "";
    }

    public function description(): string
    {
        $info = view('common.bonus-descriptions.t' . $this->item->getBonusType(), ['bonus' => $this->bonus])->render();
        return '<div class="bonus-info-wrapper"><div class="bonus-info-content">' . $info . '</div></div>';
    }

    public function blockType(): string
    {
        if ($this->bonus?->isFreeSpins()) return 'fs';
        if ($this->bonus?->isNonDeposit()) return 'no-deposit';
        if ($this->bonus?->isDeposit()) return 'to-deposit';
        return 'wager';
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

        if ($this->bonus->isNonDeposit()) {
            return view('common.slc.bonus-item-icon', [
                'img' => '/img/inventory/bezdep.png',
                'amount' => $this->amount(),
                'units' => $this->units(),
            ])->render();
        }
        return view('common.slc.shop-item-circle', ['info' => $this])->render();
    }

    public function renderItemIcon(): string
    {
        if (!empty($img = $this->item->getImage())) {
            return view('common.slc.item-user-icon', [
                'img' => $img,
                'amount' => '',
                'units' => '',
            ])->render();
        }

        if ($this->bonus?->isNonDeposit()) {
            return view('common.slc.item-icon', [
                'img' => '/img/inventory/bezdep.png',
                'amount' => $this->amount(),
                'units' => $this->units(),
            ])->render();
        }
        return view('common.slc.item-icon', [
            'img' => '/images/svg/shop-block-circle-' . $this->blockType() . '.svg',
            'amount' => $this->amount(),
            'units' => $this->units(),
        ])->render();
    }
}
