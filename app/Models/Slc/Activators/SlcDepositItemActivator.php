<?php

namespace App\Models\Slc\Activators;

use App\Helpers\Hlog;
use App\Models\Bonuses\KUserBonus;
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

class SlcDepositItemActivator implements SlcItemActivator
{

    public function activateItem(SlcUserShopItem $item, array $params = [])
    {
        $item->refresh();
        if ($item->getStatus() !== SlcUserItemStatus::New) return;

        try {
            DB::beginTransaction();

            Log::info('activating shop item ' . $item->getId());

            // get target bonus
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
            $max_mult = $bonus->getJsonParam("max-multiplier");
            $current_mult = $bonus->getJsonParam("multiplier");
            $item_amount = $item->getAmount();
            $can_add = $max_mult - $current_mult;
            if ($can_add > $item_amount) $can_add = $item_amount;

            // sub added amount from user's item
            $item->setAmount($item_amount - $can_add);
            if ($item->getAmount() <= 0) $item->setStatus(SlcUserItemStatus::Activated);
            $item->save();

            //new params
            $params = json_decode($bonus->json_params, true);
            $params['multiplier'] += $can_add;
            //recreate bonus with new params
            //recreate bonus with new params
            $new_bonus = $bonus->createFromParams(params: json_encode($params), user_id: $item->getUserId(), tmp: true, keep_params: true);
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
                SlcBonusImproveLog::add($bonus->user_id, $item->getType(), $can_add, $item->getId());

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
        /** @var KUserBonus $bonus */
        foreach ($available_bonuses as $bonus) {
            // skip bonus available
            if ($bonus->id === 0 || $bonus->id === null) continue;
            if ($bonus->isAgreementRequired() || $bonus->isPaymentRequired()) {
                $max_mult = intval($bonus->getJsonParam('max-multiplier', 0));
                // Если не установлен лимит повышения
                if ($max_mult === 0) {
                    // проверяем конфигурацию повышения по умолчанию
                    /** @var SlcBonusModsConfig $mod_conf */
                    $mod_conf = SlcBonusModsConfig::where('bonus_type', $bonus->type)->first();
                    if ($mod_conf !== null && $mod_conf->isEnabled() && $mod_conf->getDepositMod() > 0) {
                        if ($bonus->getJsonParam('multiplier', 0) > 0) {
                            $max_mult = $bonus->getJsonParam('multiplier', 0) + $mod_conf->getDepositMod();
                            $bonus->setJsonParam('max-multiplier', $max_mult);
                            $bonus->save();
                        }
                    }
                }
                // if bonus fits add to bonuses array
                if ($max_mult > 0 && $bonus->getJsonParam('multiplier', 0) < $max_mult) {
                    $bonuses[] = $bonus;
                }
            }
        }

        return view('desktop.slc.modals.select-deposit-bonus', [
            'bonuses' => $bonuses,
            'item' => $item,
        ]);
    }
}
