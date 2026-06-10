<?php

namespace App\Console\Commands;

use App\Models\PaymentSystems;
use App\Payin\Payin;
use App\PaymentsAPI\LuckyPay\LuckyPay;
use Illuminate\Console\Command;

class UpdateLuckyPayLimits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'luckypay:updatelimits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update luckypay amount limits';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $pay_systems = PaymentSystems::where('system_id', Payin::TYPE_LUCKYPAY)
            ->where('disabled', false)
            ->get();
        $api1 = new LuckyPay(1);
        $api2 = new LuckyPay(2);
        $methods[1] = collect($api1->methods()['payment_methods']);
        $methods[2] = collect($api2->methods()['payment_methods']);

        /** @var PaymentSystems $pay_method */
        foreach ($pay_systems as $pay_method) {
            $alias = explode('.', $pay_method->method_alias)[1];
            $m = 1;
            if (explode(":", $alias)[0] === '2') {
                $alias = explode(":", $alias)[1];
                $m = 2;
            }
            $method_params = $methods[$m]->where('method_id', $alias)->first();
            if (!empty($method_params) && !empty($method_params['min_buy_amount'])) {
                if ($pay_method->type == PaymentSystems::TYPE_PAYIN) {
                    if ($pay_method->getMinSum() != $method_params['min_buy_amount']) {
                        $pay_method->min_sum = $method_params['min_buy_amount'];
                    }
                    if ($pay_method->getMaxSum() != $method_params['max_buy_amount']) {
                        $pay_method->max_sum = $method_params['max_buy_amount'];
                    }
                } else {
                    // disable payout update
                    continue;
//                    if ($pay_method->getMinSum() != $method_params['min_sell_amount']) {
//                        $pay_method->min_sum = $method_params['min_sell_amount'];
//                    }
//                    if ($pay_method->getMaxSum() != $method_params['max_sell_amount']) {
//                        $pay_method->max_sum = $method_params['max_sell_amount'];
//                    }
                }
            }
            $pay_method->save();
        }
        return Command::SUCCESS;
    }
}
