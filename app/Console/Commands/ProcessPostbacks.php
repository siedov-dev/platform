<?php

namespace App\Console\Commands;

use App\MUser;
use App\PayoutTransaction;
use Illuminate\Console\Command;

class ProcessPostbacks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'processPostbacks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $users = MUser::whereNotNull('postback_params_json')->get();

        $total = 0;
        foreach ($users as $user) {
            $data = json_decode($user->postback_params_json, true);
            $lastProcessTime = date('Y-m-d H:i:s', intval($data['updatedAt']));

            $moneyPaidIn = PayoutTransaction::where('user_id', $user->id)
                ->whereIn('status_id', PayoutTransaction::getIncomingStatuses())
                ->where('date', '>', $lastProcessTime)
                ->sum('sum');

            $moneyPaidOut = PayoutTransaction::where('user_id', $user->id)
                ->whereIn('status_id', PayoutTransaction::getOutgoingStatuses())
                ->where('date', '>', $lastProcessTime)
                ->sum('sum');

            $pendingMoney = $user->money + $user->b_money_s + $user->current_money_paid_in + $user->current_money_won;

            $totalMoneySpent = $moneyPaidIn - $moneyPaidOut - $pendingMoney;

            $this->info("User $user->id: $totalMoneySpent");

            if ($totalMoneySpent > 0) {
                $url = "https://pb.gambling.pro/rev?hit_id={$data['clickId']}&act=revshare&module=kraken&secret_code=MpCvRMe8cTRVW6w7&rev_sum={$totalMoneySpent}&currency=RUB&uid={$data['uid']}&sid={$data['sid']}";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_exec($ch);
                curl_close($ch);
                $total++;
            }

            $data['updatedAt'] = time();
            $user->postback_params_json = json_encode($data);
            $user->save();
        }

        $this->info("Total users processed: $total");

        return '';
    }
}
