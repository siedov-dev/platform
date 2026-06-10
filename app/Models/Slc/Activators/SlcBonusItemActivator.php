<?php

namespace App\Models\Slc\Activators;

use App\Helpers\Hlog;
use App\Models\Bonuses\BonusFactory;
use App\Models\Bonuses\KUserBonus;
use App\Models\Slc\Achievements\SlcAchievement;
use App\Models\Slc\Achievements\SlcAchievementType;
use App\Models\Slc\SlcItem;
use App\Models\Slc\SlcUserItemStatus;
use App\Models\Slc\SlcUserShopItem;
use App\MUser;
use App\Services\KBonus\KBonus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SlcBonusItemActivator implements SlcItemActivator
{
    private SlcItem $item;
    private KUserBonus $bonus;

    public function __construct(SlcItem $item)
    {
        $this->item = $item;
        $this->bonus = KUserBonus::fromParams($this->item->getBonusType(), $this->item->getParams(), Auth::id());
    }

    public function activateItem(SlcUserShopItem $item, array $params = [])
    {
        $item->refresh();
        /** @var MUser $user */
        $user = MUser::findOrFail($item->getUserId());
        if ($item->getStatus() !== SlcUserItemStatus::New) return;

        try {
            DB::beginTransaction();

            Log::info('activating shop item '.$item->getId());

            $bonus = BonusFactory::createFromParams(
                bonus_type: $item->getBonusType(),
                user_id: $item->getUserId(),
                params: $item->getParams(),
                currency: $user->getCurrency(),
                keep_params: false,
                tmp: false,
                pattern_id: null,
                creator_type: KUserBonus::CREATOR_SLC_ITEM,
                creator_id: $item->getId(),
                title: $item->getName(),
                img_url: $item->getImage(),
                marker: 0,
                allowed_games: $item->getAllowedGames(),
            );



            if ($bonus !== null) {
                $item->setStatus(SlcUserItemStatus::Activated);
                $item->save();
                KBonus::getInstance()->addBonusToSegmentByName($bonus->getId(), "SLC Item");
                KBonus::getInstance()->addBonusToSegmentByName($bonus->getId(), "SLC Item ".$item->getItemSource());
                $user = MUser::findOrFail($item->getUserId());
                $user->setRedisValue('has_new_bonus', true);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Hlog::logException($e);
        }
    }

    public function requiresDialog(SlcUserShopItem $item): bool
    {
        if ($this->bonus->isNonDeposit()) {
            return SlcAchievement::where('type', SlcAchievementType::Bezdep)
                    ->where(function ($q) {
                        $q->whereNull('currency');
                        $q->orWhere('currency', '');
                        $q->orWhere('currency', MUser::getAuth()->currency);
                    })
                    ->first()?->isRewarded(Auth::id()) !== true;
        }
        return false;
    }

    public function getDialog(SlcUserShopItem $item): string
    {
        if ($this->bonus->isNonDeposit()) {
            $achievement = SlcAchievement::where('type', SlcAchievementType::Bezdep)->first();
            return view('common.slc.bezdep-required-dialog', compact('achievement'));
        }
        return "";
    }
}
