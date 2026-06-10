<?php

namespace App\Models\UserStatistics\DataProviders;

use App\GamesProviders\GamesManager;
use App\GamesProviders\GameTag;
use App\Models\Bonuses\UserBonus;
use App\Models\GameTransaction;
use App\MUser;

class StatCrashBetWinsData implements StatDataProvider
{
    public function getValue(MUser $user): mixed
    {
        $crash_games = GamesManager::getGamesByTag(GameTag::Crash->value);
        $game_ids = array_map(fn($game) => $game->getid(), $crash_games->getAllgames());

        $tr = GameTransaction::where('game_transactions.user_id', $user->getId())
            ->where('action', 'win')
            ->whereIn('game_transactions.game_id', $game_ids)
            ->leftJoin('users_bonuses', 'users_bonuses.id', '=', 'game_transactions.bonus')
            ->where(function ($q) {
                $q->whereNull('game_transactions.bonus')
                    ->orWhere('game_transactions.bonus', 0)
                    ->orWhere('users_bonuses.type', '=', UserBonus::TYPE_SHARED_MONEY)
                    ->orWhere('users_bonuses.type', '=', UserBonus::TYPE_SHARED_MONEY_FS);
            })
            ->selectRaw('count(game_transactions.id) as count');
//            ->where(function ($q) {
//                $q->whereNull('bonus');
//                $q->orWhere('bonus', 0);
//            });

        return $tr->first()?->count ?? 0;
//        return $tr->count();
    }
}
