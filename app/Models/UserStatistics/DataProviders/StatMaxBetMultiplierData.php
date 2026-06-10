<?php

namespace App\Models\UserStatistics\DataProviders;

use App\GamesProviders\GamesManager;
use App\GamesProviders\GameTag;
use App\Models\Bonuses\UserBonus;
use App\Models\GameTransaction;
use App\MUser;

class StatMaxBetMultiplierData implements StatDataProvider
{
    public function getValue(MUser $user): mixed
    {
        $tr = GameTransaction::where('game_transactions.user_id', $user->getId())
            ->where('action', 'win')
            ->where('last_bet_value', '>', 0)
            ->leftJoin('users_bonuses', 'users_bonuses.id', '=', 'game_transactions.bonus')
            ->where(function ($q) {
                $q->whereNull('game_transactions.bonus')
                    ->orWhere('game_transactions.bonus', 0)
                    ->orWhere('users_bonuses.type', '=', UserBonus::TYPE_SHARED_MONEY)
                    ->orWhere('users_bonuses.type', '=', UserBonus::TYPE_SHARED_MONEY_FS);
            })
//            ->where(function ($q) {
//                $q->whereNull('bonus');
//                $q->orWhere('bonus', 0);
//            })
            ->selectRaw('max(amount / last_bet_value) as mult');

        return $tr->first()?->mult?? 0;
    }
}
