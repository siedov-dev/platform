<?php

namespace Database\Seeders;

use App\Models\PaymentSystemGroup;
use App\Models\PaymentSystems;

use App\Payin\Payin;
use App\PaymentsAPI\ExPay\ExPay;
use App\UserWallet;
use App\Wallet;
use Illuminate\Database\Seeder;

class PaymentSystemExPaySeeder2 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $system_name = 'ExPay';
        $name = $system_name;
        //$image_url = '/img/paysystem/expay/usdt_tron.png';

        $cr_id = UserWallet::CURRENCY_RUB;
        $cuah_id = UserWallet::CURRENCY_UAH;
        $ck_id = UserWallet::CURRENCY_KZT;
        $cu_id = UserWallet::CURRENCY_USDT;
        $ci_id = UserWallet::CURRENCY_INR;
        $ce_id = UserWallet::CURRENCY_EUR;

        $ccc_all = [$cr_id, $cuah_id, $ck_id, $cu_id, $ci_id, $ce_id, Wallet::CURRENCY_UZS];

        $items = [
            PaymentSystems::TYPE_PAYIN => [
                ['i' => 'usdt_arb.svg', 'm' => 'USDTARB', 'n' => 'USDT ARB', 'c' => $ccc_all, 'min' => 250, 'max' => 200000],
                ['i' => 'usdc_arb.svg', 'm' => 'USDCARB', 'n' => 'USDC ARB', 'c' => $ccc_all, 'min' => 250, 'max' => 200000],
                ['i' => 'eth_arb.svg', 'm' => 'ETHARB', 'n' => 'ETH ARB', 'c' => $ccc_all, 'min' => 250, 'max' => 200000],
                ['i' => 'arb.svg', 'm' => 'ARB', 'n' => 'ARB', 'c' => $ccc_all, 'min' => 250, 'max' => 200000],
                ['i' => 'usdt_op.svg', 'm' => 'USDTOP', 'n' => 'USDT OP', 'c' => $ccc_all, 'min' => 250, 'max' => 200000],
                ['i' => 'usdc_op.svg', 'm' => 'USDCOP', 'n' => 'USDCOP', 'c' => $ccc_all, 'min' => 250, 'max' => 200000],
                ['i' => 'eth_op.svg', 'm' => 'ETH OP', 'n' => 'ETHOP', 'c' => $ccc_all, 'min' => 250, 'max' => 200000],
                ['i' => 'op.svg', 'm' => 'OP', 'n' => 'OP', 'c' => $ccc_all, 'min' => 250, 'max' => 200000],
                ['i' => 'usdc_base.svg', 'm' => 'USDCBASE', 'n' => 'USDC BASE', 'c' => $ccc_all, 'min' => 250, 'max' => 200000],
                ['i' => 'eth_base.svg', 'm' => 'ETHBASE', 'n' => 'ETH BASE', 'c' => $ccc_all, 'min' => 250, 'max' => 200000],
            ],

            PaymentSystems::TYPE_PAYOUT => [
                ['i' => 'usdt_arb.svg', 'm' => 'USDTARB', 'n' => 'USDT ARB', 'c' => $ccc_all, 'min' => 250, 'max' => 200000],
                ['i' => 'usdc_arb.svg', 'm' => 'USDCARB', 'n' => 'USDC ARB', 'c' => $ccc_all, 'min' => 250, 'max' => 200000],
                ['i' => 'eth_arb.svg', 'm' => 'ETHARB', 'n' => 'ETH ARB', 'c' => $ccc_all, 'min' => 250, 'max' => 200000],
                ['i' => 'arb.svg', 'm' => 'ARB', 'n' => 'ARB', 'c' => $ccc_all, 'min' => 250, 'max' => 200000],
                ['i' => 'usdt_op.svg', 'm' => 'USDTOP', 'n' => 'USDT OP', 'c' => $ccc_all, 'min' => 250, 'max' => 200000],
                ['i' => 'usdc_op.svg', 'm' => 'USDCOP', 'n' => 'USDCOP', 'c' => $ccc_all, 'min' => 250, 'max' => 200000],
                ['i' => 'eth_op.svg', 'm' => 'ETHOP', 'n' => 'ETH OP', 'c' => $ccc_all, 'min' => 250, 'max' => 200000],
                ['i' => 'op.svg', 'm' => 'OP', 'n' => 'OP', 'c' =>  $ccc_all, 'min' => 250, 'max' => 200000],
                ['i' => 'usdc_base.svg', 'm' => 'USDCBASE', 'n' => 'USDC BASE', 'c' => $ccc_all, 'min' => 250, 'max' => 200000],
                ['i' => 'eth_base.svg', 'm' => 'ETHBASE', 'n' => 'ETH BASE', 'c' => $ccc_all, 'min' => 250, 'max' => 200000],
            ]
        ];

        foreach($items as $type => $types){
            foreach($types as $i => $item){
                list('i' => $image, 'm' => $method_alias, 'n' => $name, 'c' => $currencies, 'min' => $min, 'max' => $max) = $item;

                $group = PaymentSystemGroup::Crypto;

                $update_field = [
                    'system_name' => $system_name,
                    'image_url' => '/img/paysystem/expay/' . $image,
                    'min_sum' => $min,
                    'max_sum' => $max,
                    'test_mode' => true,
                    'system_group' => $group,
                ];

                if(!empty($name)) $update_field['name'] = $name;

                foreach($currencies as $c){
                    PaymentSystems::updateOrCreate(
                        [
                            'type' => $type,
                            'currency_id' => $c,
                            'system_id' => Payin::TYPE_EXPAY,
                            'method_alias' => $system_name . '.' . $method_alias,
                        ],
                        $update_field
                    );
                }
            }
        }

    }
}
