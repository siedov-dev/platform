<?php

namespace Database\Seeders;

use App\Models\PaymentSystemGroup;
use App\Models\PaymentSystems;

use App\Models\User;
use App\Models\UserCurrency;
use App\Payin\Payin;
use App\PaymentsAPI\ExPay\ExPay;
use App\PaymentsAPI\PayOnHost;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentSystemPayOnHostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $system_name = class_basename(PayOnHost::class);
        $_uwc_kzt = UserWallet::CURRENCY_KZT;

        $_group_crypto = PaymentSystemGroup::Crypto;

        $items = [
            PaymentSystems::TYPE_PAYIN => [

                4 => [//crypto PaymentSystemGroup::Crypto
                    ['p' => 27579, 'w' => 30905, 's' => 4329, 'c' => $_uwc_kzt, 'min' => 1000, 'max' => 100000],
                    ['p' => 27579, 'w' => 30906, 's' => 4327, 'c' => UserWallet::CURRENCY_EUR, 'min' => 10, 'max' => 10000],
                    ['p' => 27579, 'w' => 30907, 's' => 4326, 'c' => UserWallet::CURRENCY_USD, 'min' => 10, 'max' => 10000],
                    ['p' => 27579, 'w' => 30947, 's' => 6140, 'c' => UserWallet::CURRENCY_USDT, 'min' => 10, 'max' => 10000],
                    ['p' => 27579, 'w' => 30908, 's' => 4919, 'c' => UserWallet::CURRENCY_INR, 'min' => 100, 'max' => 100000],
                    ['p' => 27579, 'w' => 30945, 's' => 3566, 'c' => UserWallet::CURRENCY_UAH, 'min' => 1000, 'max' => 100000],
                ],

                3 => [//Bank PaymentSystemGroup::Bank
                    ['a' => '27533:30755:12492', 'n' => 'STD', 'ba' => 'STD', 'c' => $_uwc_kzt],
                    ['a' => '27533:30755:12493', 'n' => 'FTD', 'ba' => 'FTD', 'c' => $_uwc_kzt],
                ],

                2 => [//P2P PaymentSystemGroup::P2p
                    ['a' => '27547:30818:12304', 'c' => UserWallet::CURRENCY_UAH],
                ],

                /*(int) PaymentSystemGroup::MobileCommerce => [
                    ['p' => 27579, 'w' => 30905, 's' => 4329, 'c' => $_uwc_kzt, 'min' => 1000, 'max' => 100000],
                ]*/

            ],
            PaymentSystems::TYPE_PAYOUT => [

                4 => [
                    ['p' => 27579, 'w' => 30910, 's' => 12555, 'c' => $_uwc_kzt, 'min' => 1000, 'max' => 100000],
                    ['p' => 27579, 'w' => 30912, 's' => 12556, 'c' => UserWallet::CURRENCY_EUR, 'min' => 10, 'max' => 10000],
                    ['p' => 27579, 'w' => 30909, 's' => 2611, 'c' => UserWallet::CURRENCY_USD, 'min' => 10, 'max' => 10000],
                    ['p' => 27579, 'w' => 30948, 's' => 10813, 'c' => UserWallet::CURRENCY_USDT, 'min' => 10, 'max' => 10000],
                    ['p' => 27579, 'w' => 30914, 's' => 12521, 'c' => UserWallet::CURRENCY_INR, 'min' => 100, 'max' => 100000],
                    ['p' => 27579, 'w' => 30947, 's' => 10666, 'c' => UserWallet::CURRENCY_UAH, 'min' => 1000, 'max' => 100000],
                ],

                3 => [//Bank PaymentSystemGroup::Bank
                    ['a' => '27547:30818:12209', 'c' => UserWallet::CURRENCY_UAH],
                    ['a' => '27533:30756:12367', 'c' => $_uwc_kzt],
                ],

            ]
        ];

        foreach($items as $type => $types){
            foreach($types as $group_id => $items) {
                foreach ($items as $i => $item) {

                    $currency_id = $item['c'] ?? null;
                    $currency_name = UserCurrency::where('id', $currency_id)->value('name');

                    $method_alias = $item['a'] ?? null;
                    if($method_alias === null) $method_alias = $item['p'] . ':' . $item['w'] . ':' . $item['s'];
                    $name = $item['n'] ?? null;
                    $min = $item['min'] ?? 1000;
                    $max = $item['max'] ?? 100000;
                    $image = $item['i'] ?? '';
                    $bank_alias = $item['ba'] ?? '';

                    if($group_id == 4){
                        $name = 'Binance Pay ' . $currency_name;
                        $image = '/img/paysystem/binance_pay.png';
                    }

                    if($group_id == 3 && $name !== null){
                        $name = $name . ' ' . $currency_name;
                    }

                    if($name === null) $name = $currency_name;

                    PaymentSystems::updateOrCreate(
                        [
                            'type' => $type,
                            'currency_id' => $currency_id,
                            'system_group' => $group_id,
                            'system_id' => Payin::TYPE_PAYONHOST,
                            'name' => $name,
                        ],
                        [
                            'method_alias' => $system_name . '.' . $method_alias,
                            'bank_alias' => ($bank_alias ? ($system_name . '.' . $currency_name . '_' . $bank_alias) : null),
                            'system_name' => $system_name,
                            'image_url' => $image,
                            'min_sum' => $min,
                            'max_sum' => $max,
                            'test_mode' => true,
                        ]
                    );

                }
            }
        }

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_PAYONHOST_PENDING],
                ['value' => 'STATUS_PAYONHOST_PENDING']
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_PAYONHOST_SUCCESS],
                ['value' => 'STATUS_PAYONHOST_SUCCESS']
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_PAYONHOST_FAIL],
                ['value' => 'STATUS_PAYONHOST_FAIL']
            );

    }

}

/*
 *
 *
 *
 *
 * wallet_id: 30945 service_id: 3566 | Payin form Binance Pay UAH
wallet_id: 30949 service_id: 6140 | Payin form Binance Pay USDT
wallet_id: 30947 service_id: 10666 | Payout Binance Pay UAH
wallet_id: 30948 service_id: 10813 | Payout Binance Pay USDT
 *
 *
 * account_id: 24819
point_id: 27579

wallet_id: 30905
service_id: 4329 | Payin form Binance Pay KZT

wallet_id: 30906
service_id: 4327 | Payin form Binance Pay EUR

wallet_id: 30907
service_id: 4326 | Payin form Binance Pay USD

wallet_id: 30908
service_id: 4919 | Payin form Binance Pay INR

wallet_id: 30909
service_id: 2611 | Payout Binance Pay USD

wallet_id: 30910
service_id: 12555 | Payout Binance Pay KZT

wallet_id: 30912
service_id: 12556 | Payout Binance Pay EUR

wallet_id: 30914
service_id: 12521 | Payout Binance Pay INR*/


