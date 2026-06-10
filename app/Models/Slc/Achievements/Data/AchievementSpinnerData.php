<?php

namespace App\Models\Slc\Achievements\Data;

use App\GamesProviders\Game;
use App\Helpers\UI;
use App\Models\GameTransaction;
use App\Models\Slc\Achievements\Data\SlcAchievementData;
use App\Models\Slc\Achievements\SlcAchievement;
use App\Models\UserStatistics\StatisticsType;
use App\Models\UserStatistics\UserStatistics;
use App\MUser;
use App\UserWallet;
use Illuminate\Support\Facades\Cache;

class AchievementSpinnerData implements SlcAchievementData
{
    const CACHE_SECONDS = 60;

    private SlcAchievement $achievement;
    private $game = null;

    /**
     * @param SlcAchievement $user
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
        if ($this->game !== null) {
            return Cache::remember($user->getId() . ':ach_value:' . $this->achievement->getId(), self::CACHE_SECONDS, function () use ($user) {
                $tr = GameTransaction::where('user_id', $user->id)
                    ->where('currency', UserWallet::currencyName($this->achievement->getCurrency()))
                    ->where('action', 'bet')
                    ->where('amount', '>', 0)
                    ->where(function ($q) {
                        $q->whereNull('bonus');
                        $q->orWhere('bonus', 0);
                    });
                $tr->where('game_id', $this->game->getId());

                return $tr->count();
            }) ?? 0;
        } else {
            return UserStatistics::getValue($user, StatisticsType::MadeBets);
        }
    }

    public function getRequiredParams(): array
    {
        return [];
    }

    public function getGoalString(MUser $user): string
    {
        $goal = __('slc.ach_spinner_goal',
            [
                'count' => $this->achievement->getTargetValue(),
            ]
        );

        if ($this->game === null) {
            $goal .= ' '.__('slc.in_any_game');
        } else {
            $goal .= ' '.__('slc.in_game'). ' <a href="'. route('games', ['name' => $this->game->getName()], false) .'">'.$this->game->getName().'</a>';
        }
        return $goal;
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
