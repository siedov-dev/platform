<?php

namespace App\Models\Slc\Achievements\Data;

use App\GamesProviders\Game;
use App\Helpers\UI;
use App\Models\Bonuses\UserBonus;
use App\Models\GameTransaction;
use App\Models\Slc\Achievements\SlcAchievement;
use App\MUser;
use App\UserWallet;
use Illuminate\Support\Facades\Cache;

class AchievementPlayerData implements SlcAchievementData
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
        $this->game = null;
        if (!empty($this->achievement->getGameCid())) {
            $this->game = \App\GamesProviders\GamesManager::getGameByCompoundId($this->achievement->getGameCid());
        }
    }

    public function getGame()
    {
        return $this->game;
    }


    public function getCurrentValue(MUser $user): int
    {
        return Cache::remember($user->getId().':ach_value:'.$this->achievement->getId(), self::CACHE_SECONDS, function () use($user) {
            $tr = GameTransaction::where('game_transactions.user_id', $user->id)
                ->where('game_transactions.currency', UserWallet::currencyName($this->achievement->getCurrency()))
                ->where('game_transactions.action', 'bet')
                ->where('game_transactions.amount', '>=', $this->achievement->getFloatParam())
                ->leftJoin('users_bonuses', 'users_bonuses.id', '=', 'game_transactions.bonus')
                ->where(function ($q) {
                    $q->whereNull('game_transactions.bonus')
                        ->orWhere('game_transactions.bonus', 0)
                        ->orWhere('users_bonuses.type', '=', UserBonus::TYPE_SHARED_MONEY)
                        ->orWhere('users_bonuses.type', '=', UserBonus::TYPE_SHARED_MONEY_FS);
                });

            if ($this->getGame() !== null) {
                $tr->where('game_transactions.game_id', $this->getGame()->getId());
            }
            $tr->selectRaw('count(game_transactions.id) as count');

            return $tr->first()?->count ?? 0;
//            return $tr->count();
        })?? 0;
    }

    public function getRequiredParams(): array
    {
        return ['min_bet' => 'float_param'];
    }

    public function getGoalString(MUser $user): string
    {
        $goal = __('slc.ach_player_goal',
            [
                'count' => $this->achievement->getTargetValue(),
                'curr_icon' => UserWallet::currencySymbol($this->achievement->getCurrency()),
                'value' => UI::prettyAmount($this->achievement->getFloatParam()),
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
        return floor($current / $target * 100);
    }
}
