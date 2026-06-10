<?php

namespace App\Console\Commands;

use App\MUser;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Console\Command;

class TestPay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'TestPay {user_id : The ID of the user} {amount : pay sum} {currency : currency}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'TestPay {user_id : The ID of the user} {amount : pay sum} {currency : currency}';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        $user = MUser::findOrFail($this->argument('user_id'));
        $amount = floatval($this->argument('amount'));
        $currency = $this->argument('currency');
        if(empty($currency)) $currency = 'RUB';
        $curr_code = UserWallet::getCurrencyCode($currency);
        $transaction = $this->createTransaction($amount, $user, $currency);

        $pay_controller = new \App\Http\Controllers\PayController();
        $pay_controller->completeTransaction($user, $transaction, $amount, 60, $curr_code);
        $this->info('Done, transaction #'.$transaction->id);
    }

    public function createTransaction(int $sum, MUser $user, $currency)
    {
        $transaction = new PayoutTransaction();
        $transaction->user_id = $user->id;
        $transaction->hall_id = UserWallet::currencyHallId(UserWallet::getCurrencyCode($currency));
        $transaction->currency = $currency;
        $transaction->hall_credits = 0;
        $transaction->date = date('Y-m-d H:i:s');
        $transaction->sum = $sum;
        $transaction->operation = 'console test payment';
        $transaction->status_id = 10;
        $transaction->type_payout_id = 'test';
        $transaction->use_bonus = 0;
        $transaction->system_id = 0;
        $transaction->method = 'console';
        $transaction->save();
        return $transaction;
    }

}
