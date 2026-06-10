<?php

namespace App\Helpers;

use App\GamesProviders\GamesManager;
use App\Models\BonusCurrencyConfig;
use App\Models\GameTransaction;
use App\Models\GloryWin;
use App\Models\TopWin;
use App\Models\UserGameStat;
use App\MUser;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixScripts
{

    public static function addDepnToTransactions()
    {
        /** @var PayoutTransaction $deposit */
        foreach (PayoutTransaction::whereIn('status_id', PayoutTransaction::getIncomingStatuses())->get() as $deposit) {
            try {
                // store deposit number
                $count = PayoutTransaction::where('user_id', $deposit->user_id)
                    ->whereIn('status_id', PayoutTransaction::getIncomingStatuses())
                    ->where('id', '<', $deposit->id)
                    ->count();
                $deposit->user_dep_n = $count + 1;
                $deposit->save();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }

    public static function totals()
    {
        $wrong_totals = UserGameStat::where('balance_diff', '!=', DB::raw('(total_bet - total_win)'))->get();
        echo('wrong_totals:'. $wrong_totals->count());

        foreach ($wrong_totals as $user_game) {
            $provider = GamesManager::getProvider($user_game->system);
//            $game = $provider?->getGame('', $user_game->game_id, true);
//            $spentAmount = $user_game->login_balance - $user_game->logout_balance;
            $user = MUser::find($user_game->user_id);
            if ($user === null) {
                echo('user not found:'. $user_game->user_id);
                continue;
            }

            $full_total_bet = GameTransaction::where('user_id', $user->id)
                ->where('currency', $user_game->currency)
                ->where('system', $user_game->system)
                ->where('game_id', $user_game->game_id)
                ->where('date', '>=', $user_game->login_date)
                ->where('date', '<=', $user_game->logout_date)
                ->where('action', 'bet')
                ->sum('amount');

            $full_total_win = GameTransaction::where('user_id', $user->id)
                ->where('currency', $user_game->currency)
                ->where('system', $user_game->system)
                ->where('game_id', $user_game->game_id)
                ->where('date', '>=', $user_game->login_date)
                ->where('date', '<=', $user_game->logout_date)
                ->where('action', '!=', 'bet')
                ->sum('amount');

            $total_bet = GameTransaction::where('user_id', $user->id)
                ->where('currency', $user_game->currency)
                ->where('system', $user_game->system)
                ->where('game_id', $user_game->game_id)
                ->where('date', '>=', $user_game->login_date)
                ->where('date', '<=', $user_game->logout_date)
                ->where('action', 'bet')
                ->whereNull('bonus')
                ->sum('amount');

            $total_win = GameTransaction::where('user_id', $user->id)
                ->where('currency', $user_game->currency)
                ->where('system', $user_game->system)
                ->where('game_id', $user_game->game_id)
                ->where('date', '>=', $user_game->login_date)
                ->where('date', '<=', $user_game->logout_date)
                ->whereNull('bonus')
                ->where('action', '!=', 'bet')
                ->sum('amount');

            $user_game->total_bet = $total_bet;
            $user_game->full_total_bet = $full_total_bet;
            $user_game->real_total_bet = $total_bet;

            $user_game->total_win = $total_win;
            $user_game->full_total_win = $full_total_win;
            $user_game->real_total_win = $total_win;
            $user_game->status = UserGameStat::STATUS_OK;

            $total_diff = $user_game->balance_diff - ($user_game->real_total_bet - $user_game->real_total_win);

            //if bonus win on some transaction lost - compensate
            if ($total_diff !== 0) {
                if ($total_diff > 0) {
                    $user_game->total_bet += abs($total_diff);
                } else {
                    $user_game->total_win += abs($total_diff);
                }
                $user_game->status = UserGameStat::STATUS_MODIFIED;
            }
            $user_game->save();
        }
    }

    public static function gameGroupInStat()
    {
        set_time_limit(600);

        /** @var UserGameStat $stat */
        foreach (UserGameStat::whereNull('group')->get() as $stat) {
            $provider = GamesManager::getProvider($stat->system);
            if ($provider !== null) {
                $game = $provider->getGame('', $stat->game_id);
                if ($game !== null) {
                    $stat->group = $game->getGroupId();
                    $stat->save();
                }
            }
        }
    }

    public static function famehall()
    {
        $game_stats = UserGameStat::query();
        $game_stats->where('created_at', '>', '2025-02-01 00:00:00');
        $game_stats->where('created_at', '<', '2025-03-06 00:00:00');
        $stats = $game_stats->get();

        /** @var UserGameStat $stat */
        foreach ($stats as $stat) {
            $win = $stat->total_win - $stat->total_bet;
            $provider = GamesManager::getProvider($stat->system);
            $game = $provider?->getGame('', $stat->game_id, true);
            $user = MUser::find($stat->user_id);
            $threshold = 1000 * BonusCurrencyConfig::getMoneyK(UserWallet::getCurrencyCode($stat->currency));
            if ($win >= $threshold && $user && $game) {
                $gwin = new GloryWin();
                $gwin->user_id = $user->id;
                $gwin->system = $stat->system;
                $gwin->game_id = $stat->game_id;
                $gwin->bet = 0;
                $gwin->win = $win;
                $gwin->currency = UserWallet::getCurrencyCode($stat->currency);
                $gwin->save();
                $gwin->created_at = $stat->created_at;
                $gwin->save();
            }
        }

    }

    public static function fixRefundedBets()
    {
        GameTransaction::where('action', 'refund')->whereNotNull('bet_transaction_id')->chunk(200, function ($transactions) {
            dump($transactions->pluck('bet_transaction_id')->toArray());
            $ids = array_filter($transactions->pluck('bet_transaction_id')->toArray(), function ($id) { return $id !== "";});
            dump($ids);
            if (!empty($ids)) GameTransaction::whereIn('transaction_id', $ids)->update(['refunded' => true]);
        });
    }

}
