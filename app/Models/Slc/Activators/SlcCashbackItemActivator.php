<?php

namespace App\Models\Slc\Activators;

use App\Helpers\Hlog;
use App\Models\Bonuses\UserBonus;
use App\Models\CashBackPlusLog;
use App\Models\Slc\SlcBonusImproveLog;
use App\Models\Slc\SlcUserItemStatus;
use App\Models\Slc\SlcUserShopItem;
use App\MUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SlcCashbackItemActivator implements SlcItemActivator
{

    public function activateItem(SlcUserShopItem $item, array $params = [])
    {
        /** @var MUser $user */
        $user = MUser::findOrFail($item->getUserId());
        $current_value = $user->getValue('cashback_plus', 0);

        if ($current_value === 100) return;

        $current_value += $item->getAmount();
        if ($current_value > 100) $current_value = 100;
        $user->setValue('cashback_plus', $current_value);
        CashBackPlusLog::log($user->getId(), $current_value);
        $item->setStatus(SlcUserItemStatus::Activated);
        $item->save();
    }

    public function requiresDialog(SlcUserShopItem $item): bool
    {
        return true;
    }

    public function getDialog(SlcUserShopItem $item): string
    {
        /** @var MUser $user */
        $user = MUser::findOrFail($item->getUserId());
        $current_value = $user->getValue('cashback_plus', 0);

        return view('desktop.slc.modals.cashback-item-dialog', [
            'item' => $item,
            'current_value' => $current_value,
        ]);
    }
}
