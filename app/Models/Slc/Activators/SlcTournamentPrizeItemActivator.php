<?php

namespace App\Models\Slc\Activators;

use App\Helpers\Hlog;
use App\Models\Bonuses\UserBonus;
use App\Models\CashBackPlusLog;
use App\Models\Slc\SlcBonusImproveLog;
use App\Models\Slc\SlcUserInventory;
use App\Models\Slc\SlcUserItemStatus;
use App\Models\Slc\SlcUserShopItem;
use App\Models\Tournaments\TournamentSlcItem;
use App\MUser;
use App\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SlcTournamentPrizeItemActivator implements SlcItemActivator
{

    public function activateItem(SlcUserShopItem $item, array $params = [])
    {
        /** @var MUser $user */
        $user = MUser::findOrFail($item->getUserId());
        try {
            DB::beginTransaction();
            /** @var SlcUserShopItem $item */
            $item = SlcUserShopItem::where('id', $item->getId())
                ->where('status', SlcUserItemStatus::New->value)
                ->LockForUpdate()->first();
            if ($item !== null) {
                $item->setStatus(SlcUserItemStatus::Activated);
                $item->save();
                $params = json_decode($item->getParams(), true);
                if (!empty($params['item'])) {
                    $win_item = new TournamentSlcItem($params['item']);
                    $inventory = SlcUserInventory::forUser($user);
                    $inventory->addItem($win_item, 'tournament prize');
                }
                $amount = $params['money']['amount']?? 0;
                if ($amount > 0) {
                    $user->getWallet(Wallet::getCurrencyCode($params['money']['currency']))->addMoney($amount);
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Hlog::logException($e);
        } finally {
            DB::commit();
        }

    }

    public function requiresDialog(SlcUserShopItem $item): bool
    {
        return true;
    }

    public function getDialog(SlcUserShopItem $item): string
    {
        /** @var MUser $user */
        $user = MUser::findOrFail($item->getUserId());

        $params = json_decode($item->getParams(), true);
        $win_item = null;
        if (!empty($params['item'])) {
            $win_item = new TournamentSlcItem($params['item']);
        }

        return view('desktop.slc.modals.tournament-item-dialog', [
            'item' => $item,
            'win_item' => $win_item,
            'place' => $params['place']?? 0,
            'amount' => $params['money']['amount']?? 0,
            'currency' => $params['money']['currency']?? '',
            'can_activate' => $user->isEmailVerified(),
        ]);
    }
}
