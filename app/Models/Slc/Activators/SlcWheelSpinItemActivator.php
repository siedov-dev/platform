<?php

namespace App\Models\Slc\Activators;

use App\Helpers\Hlog;
use App\Models\Bonuses\UserBonus;
use App\Models\Slc\SlcUserItemStatus;
use App\Models\Slc\SlcUserShopItem;
use App\MUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SlcWheelSpinItemActivator implements SlcItemActivator
{

    public function activateItem(SlcUserShopItem $item, array $params = [])
    {
        $item->refresh();
        if ($item->getStatus() !== SlcUserItemStatus::New) return;

        try {
            DB::beginTransaction();
            Log::info('activating shop item '.$item->getId());
            /** @var MUser $user */
            $user = MUser::findOrFail($item->getUserId());
            $user->setValue('wheel_spins', $user->getValue('wheel_spins', 0) + $item->getAmount());
            $item->setAmount(0);
            $item->setStatus(SlcUserItemStatus::Activated);
            $item->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Hlog::logException($e);
        }
    }

    public function requiresDialog(SlcUserShopItem $item): bool
    {
        return false;
    }

    public function getDialog(SlcUserShopItem $item): string
    {
       return "";
    }
}
