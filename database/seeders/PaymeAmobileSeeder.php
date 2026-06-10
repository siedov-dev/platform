<?php

namespace Database\Seeders;

use App\Models\PaymentSystems;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymeAmobileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $system_name = 'Payme';
        $image_dir = '/img/paysystem/';

        $items = [
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => Wallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'RUB_amobile:card_number',
                'min' => 1600,
                'max' => 300000,
                'name' => 'A-mobile card',
            ],

            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => Wallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'RUB_amobile:by_mobile',
                'min' => 1601,
                'max' => 300000,
                'name' => 'A-mobile phone',
            ],

            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => Wallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'RUB_fiat:card_number',
                'min' => 1600,
                'max' => 300000,
                'name' => 'fiat card',
            ],

            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => Wallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'RUB_fiat:by_mobile',
                'min' => 1601,
                'max' => 300000,
                'name' => 'fiat phone',
            ],

        ];

        foreach ($items as $item) {

            PaymentSystems::updateOrCreate(
                [
                    'type' => $item['type'],
                    'currency_id' => $item['currency_id'],
                    'method_alias' => $item['method_alias'],
                    'system_id' => Payin::TYPE_PAYME,
                    'system_name' => $system_name,
                ],
                [
                    'name' => $item['name'],
                    'image_url' => $image_dir . 'visa.png',
                    'min_sum' => $item['min'],
                    'max_sum' => $item['max'],
                    'test_mode' => true,
                ]
            );
        }
    }
}
