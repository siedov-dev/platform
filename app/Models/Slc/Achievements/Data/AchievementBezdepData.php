<?php

namespace App\Models\Slc\Achievements\Data;

use App\GamesProviders\Game;
use App\Helpers\UI;
use App\Models\GameTransaction;
use App\Models\Slc\Achievements\Data\SlcAchievementData;
use App\Models\Slc\Achievements\SlcAchievement;
use App\Models\UserMoneyStat;
use App\MUser;
use App\UserWallet;
use Illuminate\Support\Facades\Cache;

class AchievementBezdepData implements SlcAchievementData
{
//    const CACHE_SECONDS = 60;

    private SlcAchievement $achievement;
//    private $game = null;

    /**
     * @param SlcAchievement $achievement
     */
    public function __construct(SlcAchievement $achievement)
    {
        $this->achievement = $achievement;
        if (!empty($this->achievement->getGameCid())) {
            $this->game = \App\GamesProviders\GamesManager::getGameByCompoundId($this->achievement->getGameCid());
        }
    }


    public function getCurrentValue(MUser $user): int
    {
        $po = UserMoneyStat::getStat($user, $this->achievement->getCurrency(), UserMoneyStat::TYPE_TOTAL);
        return $po->getDepositCount();
//        return Cache::remember($user->getId().':ach_value:'.$this->achievement->getId(), self::CACHE_SECONDS, function () use($user) {
//            $tr = GameTransaction::where('user_id', $user->id)
//                    ->where('currency', UserWallet::currencyName($this->achievement->getCurrency()))
//                    ->where('action', 'bet')
//                    ->where(function ($q) {
//                        $q->whereNull('bonus');
//                        $q->orWhere('bonus', 0);
//                    });
//
//            if ($this->game !== null) {
//                $tr->where('game_id', $this->game->getId());
//            }
//
//            return $tr->sum('amount');
//        })?? 0;
    }

    public function getRequiredParams(): array
    {
        return [];
    }

    public function getGoalString(MUser $user): string
    {
        return __('slc.ach_dep_goal_all',
            [
                'count' => $this->achievement->getTargetValue(),
            ]
        );
    }

    public function getValueHint(MUser $user): string
    {
        return $this->getCurrentValue($user);
    }

    public function getCurrentPercent(MUser $user): int
    {
        $target = $this->achievement->getTargetValue();
        $current = $this->getCurrentValue($user);
        if ($target === 0 || $current > $target) return 100;
        return floor($current / $target * 100) ;
    }
}
