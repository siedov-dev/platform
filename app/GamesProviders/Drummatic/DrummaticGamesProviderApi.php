<?php

namespace App\GamesProviders\DrumMatic;

use App\Events\MoneySpent;
use App\GamesProviders\GameProviderApi;
use App\Models\DmGame;
use App\Models\TopWin;
use App\MUser;
use App\PayoutTransaction;

class DrummaticGamesProviderApi extends GameProviderApi
{

    public function startSession(MUser $user, string $game_id, string $session_id = ""): bool
    {
        // TODO: Implement startSession() method.
    }

    public function transferMoneyInGame(MUser $user, float $amount): bool
    {
        // TODO: Implement transferMoneyInGame() method.
    }

    public function transferMoneyBack(MUser $user): bool
    {
        // TODO: Implement transferMoneyBack() method.
    }

    public function closeSession(MUser $user, string $session_id = ""): bool
    {
        if ($user->current_money_paid_in === 0 || $user->current_money_won === 0) {
            return true;
        }

        $dm = DmGame::where('user_id', $user->id)
            ->whereNull('logout_date')
            ->latest('login_date')
            ->first();

        if ($dm) {
            $transaction = new PayoutTransaction();
            $transaction->user_id = $user->id;
            $transaction->hall_id = $user->hall_id;
            $transaction->status_id = 56;
            $transaction->operation = 'Возвращение средств на общий счет.';
            $transaction->sum = $user->b_money_s + $user->current_money_paid_in + $user->current_money_won;
            $transaction->date = date('Y-m-d H:i:s');
            $transaction->save();

            $dm->logout_date = date('Y-m-d H:i:s');
            $dm->logout_balance = $transaction->sum;
            $dm->save();

            // Close all old sessions
            DmGame::where('user_id', $user->id)
                ->whereNull('logout_date')
                ->update([
                    'logout_date' => date('Y-m-d H:i:s')
                ]);

            $spentAmount = $dm->login_balance - $dm->logout_balance;
            event(new MoneySpent($user, $spentAmount, $user->getCurrencyName()));
            TopWin::userWin($user, -$spentAmount, $dm->game_id, $user->dm_currency, 'drummatic', 'drummatic');

            $user->money = $user->money + $user->b_money_s + $user->current_money_paid_in + $user->current_money_won;
            $user->b_money_s = 0;
            $user->current_money_paid_in = 0;
            $user->current_money_won = 0;

            //transfer dm money to wallet
            if($user->money > 0){
                $user->addMoney($user->money, $user->dm_currency);
                $user->money = 0;
            }
            $user->save();
        }
        return true;
    }


    public function getAllGames(): array
    {
        // TODO: Implement getAllGames() method.
    }

    public function getBalance(MUser $user, string &$currency): float
    {
        return $user->b_money_s + $user->current_money_paid_in + $user->current_money_won;
    }
}
