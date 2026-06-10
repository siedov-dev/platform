<?php

namespace App\Models\Slc\Activators;

use App\Helpers\Hlog;
use App\Models\Slc\SlcBonusImproveLog;
use App\Models\Slc\SlcBonusModsConfig;
use App\Models\Slc\SlcUserItemStatus;
use App\Models\Slc\SlcUserShopItem;
use App\MUser;
use App\Services\KBonus\KBonus;
use App\Services\KSocket\KSocket;
use App\Services\Users\UserManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SlcWagerItemActivator implements SlcItemActivator
{

    public function activateItem(SlcUserShopItem $item, array $params = [])
    {
        $item->refresh();
        if ($item->getStatus() !== SlcUserItemStatus::New) return;

        try {
            DB::beginTransaction();

            Log::info('activating shop item '.$item->getId());

//            // get target bonus
//            $bonus = UserBonus::where('id', $params['bonus_id'])
//                ->where('user_id', $item->getUserId())
//                ->whereIn('status', [
//                    UserBonus::STATUS_NOT_ACTIVATED,
//                    UserBonus::STATUS_WAITING_FOR_AGREEMENT,
//                    UserBonus::STATUS_WAITING_FOR_PAYMENT,
//                ])
//                ->first();
//
//            $bonus = UserBonus::convertByType($bonus);
            $bonus = KBonus::getInstance()->getBonus($params['bonus_id']);
            if ($bonus === null) return;
            if ($bonus->user_id !== $item->getUserId()) return;
            if (!$bonus->isAgreementRequired() && !$bonus->isPaymentRequired()) {
                return;
            }

            // calc max deposit amount can be added
            $min_wager = $bonus->getJsonParam("min_wager");
            $current_wager = $bonus->getJsonParam("wager");
            $item_amount = $item->getAmount();
            $can_sub = $current_wager - $min_wager;
            if ($can_sub > $item_amount) $can_sub = $item_amount;

            // sub added amount from user's item
            $item->setAmount($item_amount - $can_sub);
            if ($item->getAmount() <= 0) $item->setStatus(SlcUserItemStatus::Activated);
            $item->save();

            //new params
            $params = json_decode($bonus->json_params, true);
            $params['wager'] -= $can_sub;
            //recreate bonus with new params
            $new_bonus = $bonus->createFromParams( params: json_encode($params), user_id: $item->getUserId(), tmp: true, keep_params: true);
            $new_bonus->status = $bonus->status;
            $new_bonus->id = $bonus->getId();
            $new_bonus->game_id = $bonus->game_id;
            $new_bonus->game_provider = $bonus->game_provider;
            $new_bonus->type = $bonus->type;
            $new_bonus->title = $bonus->title;
            $new_bonus->img_url = $bonus->img_url;
            $new_bonus->allowed_games = $bonus->allowed_games;
            $new_bonus->creator_id = $bonus->creator_id;
            $new_bonus->creator_type = $bonus->creator_type;
            $new_bonus->marker = $bonus->marker;
            if ($new_bonus->save()) {

                KSocket::getInstance()->sendEvent($item->getUserId(), "bonus:changed");
//            $bonus->setRawAttributes($new_bonus->getAttributes());
//            $bonus->save();
                SlcBonusImproveLog::add($bonus->user_id, $item->getType(), $can_sub, $item->getId());

                DB::commit();
            } else {
                DB::rollBack();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Hlog::logException($e);
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

        // get all available bonuses
//        $available_bonuses = UserBonus::where('user_id', $user->getId())
//            ->whereIn('status', [
//                UserBonus::STATUS_NOT_ACTIVATED,
//                UserBonus::STATUS_WAITING_FOR_AGREEMENT,
//                UserBonus::STATUS_WAITING_FOR_PAYMENT,
//            ])
//            ->get();
        $available_bonuses = KBonus::getInstance()->getUserBonuses($user->getId(), $user->getCurrency());
        // appropriate bonuses
        $bonuses = [];
        foreach ($available_bonuses as $bonus) {
            // skip bonus available
            if ($bonus->id === 0 || $bonus->id === null) continue;
//            // skip wrong status
//            if (!in_array($bonus->status, [
//                UserBonus::STATUS_NOT_ACTIVATED,
//                UserBonus::STATUS_WAITING_FOR_AGREEMENT,
//                UserBonus::STATUS_WAITING_FOR_PAYMENT,
//            ])) continue;
//            $bonus = UserBonus::convertByType($av_bonus);
            if ($bonus->isAgreementRequired() || $bonus->isPaymentRequired()) {
                $min_wager = intval($bonus->getJsonParam('min_wager', 0));
                // Если не установлен лимит понижения
                if ($min_wager === 0) {
                    // проверяем конфигурацию повышения по умолчанию
                    /** @var SlcBonusModsConfig $mod_conf */
                    $mod_conf = SlcBonusModsConfig::where('bonus_type', $bonus->type)->first();
                    if ($mod_conf !== null && $mod_conf->isEnabled() && $mod_conf->getWagerMod() > 0) {
                        if ($bonus->getJsonParam('wager', 0) > 0) {
                            $min_wager = $bonus->getJsonParam('wager', 0) - $mod_conf->getWagerMod();
                            if ($min_wager > 0) {
                                $bonus->setJsonParam('min_wager', $min_wager);
                                $bonus->save();
                            }
                        }
                    }
                }
                // if bonus fits add to bonuses array
                if ($min_wager > 0 && $bonus->getJsonParam('wager', 0) > $min_wager) {
                    $bonuses[] = $bonus;
                }
            }
        }

        return view('desktop.slc.modals.select-wager-bonus', [
            'bonuses' => $bonuses,
            'item' => $item,
        ]);
    }
}
