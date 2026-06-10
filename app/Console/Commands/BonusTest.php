<?php

namespace App\Console\Commands;

use App\Models\Bonuses\UserBonus;
use App\MUser;
use App\UserWallet;
use App\VegasWallet;
use Illuminate\Console\Command;

/**
 * @deprecated
 */
class BonusTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bonus:test {bonus_id : bonus id} {bet : bet sum} {win : win sum} {count : bets count}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test UserBonus';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /** @var UserBonus $bonus */
        $bonus = UserBonus::find($this->argument('bonus_id'));
        if ($bonus === null) {
            $this->error('bonus not found');
            return Command::FAILURE;
        }
        /** @var MUser $user */
        $user = $bonus->user;
        if ($user === null) {
            $this->error('user not found');
            return Command::FAILURE;
        }
        $this->info('converting bonus');
        $bonus = UserBonus::convertByType($bonus);

        $this->info('get vegas wallet');
        $wallet = VegasWallet::getForUser($user);

        $this->info('bonus start status:'. $bonus->getStatusString($wallet));
        $this->info('bonus start balance:'. $bonus->getBalanceString());

        $cnt = $this->argument('count');
        $bet = $this->argument('bet');
        $win = $this->argument('win');

        $gameId = 'blood_ag';

        for ($c = 1; $c <= $cnt; $c++) {
            $this->info('bet '.$c . ' ' . $bet.$bonus->getCurrencyName());
            $bonus->withdrawCents($bet * 100, $wallet, 'vegas', $gameId);
            $bonus->actualizeVegas($gameId, $wallet);
            $bonus->save();
            $this->info('bonus balance:'. $bonus->getBalanceString());
            $this->info('win '.$c . ' ' . $win.$bonus->getCurrencyName());
            $bonus->depositCents($win * 100, $wallet, 'vegas', $gameId);
            $bonus->actualizeVegas($gameId, $wallet);
            $bonus->save();
            $this->info('bonus balance:'. $bonus->getBalanceString());
        }

        $this->info('bonus status:'. $bonus->getStatusString($wallet));
        return Command::SUCCESS;
    }
}
