<?php

namespace Database\Seeders;

use App\Models\PaymentSystemGroup;
use App\Models\PaymentSystems;

use App\Payin\Payin;
use App\PaymentsAPI\ExPay\ExPay;
use App\UserWallet;
use Illuminate\Database\Seeder;

class PaymentSystemExPaySeeder extends Seeder
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

        $ccc_all = [$cr_id, $cuah_id, $ck_id, $cu_id, $ci_id, $ce_id];

        $items = [
            //['type' => PaymentSystems::TYPE_PAYIN, 'method_alias' => 'USDTTRC', 'name' => 'USDT TRC20', 'c' => UserWallet::CURRENCY_USDT],
            PaymentSystems::TYPE_PAYIN => [
                ['i' => 'bitcoin.svg', 'm' => 'BTC', 'n' => 'BTC', 'c' => $ccc_all, 'min' => 1000, 'max' => 250000],
                ['i' => 'ethereum.svg', 'm' => 'ETH', 'n' => 'ETH', 'c' => $ccc_all, 'min' => 1000, 'max' => 250000],
                ['i' => 'tron.svg', 'm' => 'TRX', 'n' => 'TRX', 'c' => $ccc_all, 'min' => 500, 'max' => 100000],
                ['i' => 'ton.svg', 'm' => 'TON', 'n' => 'TON', 'c' => $ccc_all, 'min' => 500, 'max' => 100000],
                ['i' => 'doge.svg', 'm' => 'DOGE', 'n' => 'DOGE', 'c' => $ccc_all, 'min' => 500, 'max' => 100000],
                ['i' => 'bch.svg', 'm' => 'BCH', 'n' => 'BCH', 'c' => $ccc_all, 'min' => 500, 'max' => 100000],
                ['i' => 'xrp.svg', 'm' => 'XRP', 'n' => 'XRP', 'c' => $ccc_all, 'min' => 250, 'max' => 200000],
                ['i' => 'ltc.svg', 'm' => 'LTC', 'n' => 'LTC', 'c' => $ccc_all, 'min' => 250, 'max' => 200000],
                ['i' => 'usdt_bsc.svg', 'm' => 'USDTBSC', 'n' => 'USDT BEP20', 'c' => $ccc_all, 'min' => 250, 'max' => 200000],
                ['i' => 'usdt_tron.png', 'm' => 'USDTTRC', 'n' => 'USDT TRC20', 'c' => $ccc_all, 'min' => 250, 'max' => 200000],
                ['i' => 'usdt_erc.svg', 'm' => 'USDTERC', 'n' => 'USDT ERC20', 'c' => $ccc_all, 'min' => 250, 'max' => 200000],
            ],

            PaymentSystems::TYPE_PAYOUT => [
                ['i' => 'sberbank.png', 'n' => 'Сбербанк', 'm' => 'CARDRUBP2P_SBERRUB', 'c' => [$cr_id], 'min' => 1000, 'max' => 250000],
                ['i' => 'tinkoff.png', 'n' => 'Tinkoff', 'm' => 'CARDRUBP2P_TCSBRUB', 'c' => [$cr_id], 'min' => 1000, 'max' => 250000],
                ['i' => 'sbp.png', 'n' => 'СПБ', 'm' => 'CARDRUBP2P_SBPRUB', 'c' => [$cr_id], 'min' => 1000, 'max' => 250000],
                ['i' => 'usdt_tron.png', 'm' => 'USDTTRC', 'n' => 'USDT TRC20', 'c' => $ccc_all, 'min' => 1000, 'max' => 200000, 'g' => PaymentSystemGroup::Crypto],
                ['i' => 'bitcoin.svg', 'm' => 'BTC', 'n' => 'BTC', 'c' => $ccc_all, 'min' => 1000, 'max' => 250000, 'g' => PaymentSystemGroup::Crypto],
                ['i' => 'ethereum.svg', 'm' => 'ETH', 'n' => 'ETH', 'c' => $ccc_all, 'min' => 1000, 'max' => 250000, 'g' => PaymentSystemGroup::Crypto],
                ['i' => 'tron.svg', 'm' => 'TRX', 'n' => 'TRX', 'c' => $ccc_all, 'min' => 500, 'max' => 100000, 'g' => PaymentSystemGroup::Crypto],
                ['i' => 'ton.svg', 'm' => 'TON', 'n' => 'TON', 'c' => $ccc_all, 'min' => 500, 'max' => 100000, 'g' => PaymentSystemGroup::Crypto],
                ['i' => 'doge.svg', 'm' => 'DOGE', 'n' => 'DOGE', 'c' => $ccc_all, 'min' => 500, 'max' => 100000, 'g' => PaymentSystemGroup::Crypto],
                ['i' => 'bch.svg', 'm' => 'BCH', 'n' => 'BCH', 'c' => $ccc_all, 'min' => 500, 'max' => 100000, 'g' => PaymentSystemGroup::Crypto],
                //['i' => 'xrp.svg', 'm' => 'XRP', 'n' => 'XRP', 'c' => $ccc_all, 'min' => 250, 'max' => 200000, 'g' => PaymentSystemGroup::Crypto],
                ['i' => 'ltc.svg', 'm' => 'LTC', 'n' => 'LTC', 'c' => $ccc_all, 'min' => 250, 'max' => 200000, 'g' => PaymentSystemGroup::Crypto],
                ['i' => 'usdt_bsc.svg', 'm' => 'USDTBSC', 'n' => 'USDT BEP20', 'c' => $ccc_all, 'min' => 250, 'max' => 200000, 'g' => PaymentSystemGroup::Crypto],
                ['i' => 'usdt_tron.png', 'm' => 'USDTTRC', 'n' => 'USDT TRC20', 'c' => $ccc_all, 'min' => 250, 'max' => 200000, 'g' => PaymentSystemGroup::Crypto],
                ['i' => 'usdt_erc.svg', 'm' => 'USDTERC', 'n' => 'USDT ERC20', 'c' => $ccc_all, 'min' => 250, 'max' => 200000, 'g' => PaymentSystemGroup::Crypto],
            ]

        ];

        //P2P RUB in
        PaymentSystems::updateOrCreate(
            [
                'system_id' => Payin::TYPE_EXPAY,
                'type' => PaymentSystems::TYPE_PAYIN,
                'method_alias' => 'ExPay.RUBCISP2P',
                'currency_id' => $cr_id,
            ],
            [
                'name' => 'ExPay P2P RUB',
                'system_name' => $system_name,
                'image_url' => '/img/paysystem/expay/logo.svg',
                'min_sum' => 1000,
                'max_sum' => 100000,
                'system_group' => 2,
            ]
        );

        //USDTTRC in
        PaymentSystems::where([
                'system_id' => Payin::TYPE_EXPAY,
                'type' => PaymentSystems::TYPE_PAYIN,
                'method_alias' => 'ExPay.USDTTRC_',
            ])
            ->update([
                'name' => 'ExPay USDT TRC20',
                'method_alias' => 'ExPay.USDTTRC',
                'image_url' => '/img/paysystem/expay/usdt_tron.png',
                'min_sum' => 2,
                'max_sum' => 10000,
                'system_group' => 4,
            ]);

        foreach($items as $type => $types){
            foreach($types as $i => $item){
                list('i' => $image, 'm' => $method_alias, 'n' => $name, 'c' => $currencies, 'min' => $min, 'max' => $max) = $item;

                $group = $item['g'] ?? ($type === 1 ? 2 : 4);

                $update_field = [
                    'system_name' => $system_name,
                    'image_url' => '/img/paysystem/expay/' . $image,
                    'min_sum' => $min,
                    'max_sum' => $max,
                    //'test_mode' => true,
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
