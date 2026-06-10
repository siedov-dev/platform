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
use App\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PayOnHost2Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $system_name = 'PayOnHost';

        $items = [
            ['p' => 27533, 'w' => 33155, 's' => 13315, 'c' => Wallet::CURRENCY_KZT, 'min' => 1000, 'max' => 100000, 'n' => 'MC KZT FTD'],
            ['p' => 27533, 'w' => 33155, 's' => 13316, 'c' => Wallet::CURRENCY_KZT, 'min' => 1000, 'max' => 100000, 'n' => 'MC KZT STD'],
        ];

        foreach ($items as $item) {
            $currency_id = $item['c'] ?? null;
            $currency_name = Wallet::currencyName($currency_id);

            $method_alias = $item['a'] ?? null;
            if ($method_alias === null) $method_alias = $item['p'] . ':' . $item['w'] . ':' . $item['s'];
            $name = $item['n'] ?? null;
            $min = $item['min'] ?? 1000;
            $max = $item['max'] ?? 100000;
            $image = $item['i'] ?? '';

            if ($name === null) $name = $currency_name;

            PaymentSystems::updateOrCreate(
                [
                    'type' => PaymentSystems::TYPE_PAYIN,
                    'currency_id' => $currency_id,
                    'system_id' => Payin::TYPE_PAYONHOST,
                    'name' => $name,
                    'method_alias' => $system_name . '.' . $method_alias,
                ],
                [
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

/*
 *
 account_id: 24819
point_id: 27533
wallet_id: 33155 KZT

service_id: 13315 | Payin form MC KZT FTD
service_id: 13316 | Payin form MC KZT STD

Your credentials to use API:

point_id — 27533
*/


