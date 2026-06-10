<?php

namespace App\Models\Slc\Activators;

use App\Helpers\Hlog;
use App\Models\Bonuses\BonusFactory;
use App\Models\Bonuses\KUserBonus;
use App\Models\Slc\Achievements\SlcAchievement;
use App\Models\Slc\Achievements\SlcAchievementType;
use App\Models\Slc\BezdepItemConfig;
use App\Models\Slc\SlcUserItemStatus;
use App\Models\Slc\SlcUserShopItem;
use App\MUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SlcBezdepItemActivator implements SlcItemActivator
{

    public function activateItem(SlcUserShopItem $item, array $params = [])
    {
        $item->refresh();
        /** @var MUser $user */
        $user = MUser::findOrFail($item->getUserId());
        if ($item->getStatus() !== SlcUserItemStatus::New) return;
        if ($item->created_at->diffInDays(now()) < 1) {

            if (SlcAchievement::where('type', SlcAchievementType::Bezdep)
                    ->where(function ($q) use ($user) {
                        $q->whereNull('currency');
                        $q->orWhere('currency', '');
                        $q->orWhere('currency', $user->currency);
                    })
                    ->first()?->isRewarded($item->getUserId()) !== true) return;
        }

        try {
            DB::beginTransaction();

            Log::info('activating shop item '.$item->getId());

            /** @var BezdepItemConfig $params */
            $params = BezdepItemConfig::first();
            $bonus_params = json_decode($params->getParams(), true);
            $bezdep_params = json_decode($item->getParams(), true);

            // override bezdep freespins amount if set in item
            if (($bezdep_params['freespins']?? 0) > 0) {
                $bonus_params['freespins'] = $bezdep_params['freespins'];
                //and for all currencies
                foreach ($bonus_params as $param => $val) {
                    if (preg_match('#^params_(.*)$#', $param, $matches)) {
                        $bonus_params[$matches[0]]['freespins'] = $bezdep_params['freespins'];
                    }
                }
            }

            $bonus = BonusFactory::createFromParams(
                bonus_type: $params->getBonusType(),
                user_id: $item->getUserId(),
                params: json_encode($bonus_params),
                currency: $user->getCurrency(),
                keep_params: false,
                tmp: false,
                pattern_id: null,
                creator_type: KUserBonus::CREATOR_SLC_ITEM,
                creator_id: $item->getId(),
                title: $params->getTitle(),
                img_url: $params->getImgUrl(),
                marker: 0,
                allowed_games: $params->getGames(),
            );

//            $bonus = UserBonus::fromParams(
//                bonus_type: $params->getBonusType(),
//                params: json_encode($bonus_params),
//                user_id: $item->getUserId(),
//                tmp: true,
//            );


            if ($bonus !== null) {
//                $bonus->setAllowedGames($params->getGames());
//                $bonus->title = $params->getTitle();
//                $bonus->img_url = $params->getImgUrl();
//                $bonus->save();
                $item->setStatus(SlcUserItemStatus::Activated);
                $item->save();
                MUser::getAuth()?->setRedisValue('has_new_bonus', true);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Hlog::logException($e);
        }
    }

    public function requiresDialog(SlcUserShopItem $item): bool
    {
        // Если итем создан больше сутки
        if ($item->created_at->diffInDays(now()) >= 1) return false;

        // Проверяем наличие достижения "Бездеп"
        return SlcAchievement::where('type', SlcAchievementType::Bezdep)
                ->where(function ($q) {
                    $q->whereNull('currency');
                    $q->orWhere('currency', '');
                    $q->orWhere('currency', MUser::getAuth()->currency);
                })
                ->first()?->isRewarded(Auth::id()) !== true;
    }

    public function getDialog(SlcUserShopItem $item): string
    {
        $achievement = SlcAchievement::where('type', SlcAchievementType::Bezdep)
            ->where(function ($q) {
                $q->whereNull('currency');
                $q->orWhere('currency', '');
                $q->orWhere('currency', MUser::getAuth()->currency);
            })
            ->first();
        return view('common.slc.bezdep-required-dialog', [
            'achievement' => $achievement,
            'item' => $item,
        ]);
    }
}
