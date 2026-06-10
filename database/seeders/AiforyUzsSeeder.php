<?php

namespace Database\Seeders;

use App\Models\PaymentSystems;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AiforyUzsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $system_name = 'Aifory';
        $image_dir = '/img/paysystem/';

        $items = [
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_UZS,
                'method_alias' => $system_name . '.' . 'm_606',
                'min' => 30000,
                'max' => 33000000,
                'name' => 'ANY UZS',
            ],
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_UZS,
                'method_alias' => $system_name . '.' . 'm_607',
                'min' => 30000,
                'max' => 33000000,
                'name' => 'HUMO UZS',
            ],
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_UZS,
                'method_alias' => $system_name . '.' . 'm_608',
                'min' => 30000,
                'max' => 33000000,
                'name' => 'UZCARD',
            ],
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_UZS,
                'method_alias' => $system_name . '.' . 'm_609',
                'min' => 30000,
                'max' => 33000000,
                'name' => 'PayMe',
            ],
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_UZS,
                'method_alias' => $system_name . '.' . 'm_610',
                'min' => 50000,
                'max' => 20000000,
                'name' => 'Click QR',
            ],
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_UZS,
                'method_alias' => $system_name . '.' . 'm_611',
                'min' => 30000,
                'max' => 10000000,
                'name' => 'Pay Me Mobile',
            ],
            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => UserWallet::CURRENCY_UZS,
                'method_alias' => $system_name . '.' . 'UZS_1',
                'min' => 30000,
                'max' => 33000000,
                'name' => 'UZS Card',
            ],
        ];

        foreach ($items as $item) {

            PaymentSystems::updateOrCreate(
                [
                    'type' => $item['type'],
                    'currency_id' => $item['currency_id'],
                    'method_alias' => $item['method_alias'],
                    'system_id' => Payin::TYPE_AIFORY,
                    'system_name' => $system_name,
                ],
                [
                    'name' => $item['name'],
                    'image_url' => $image_dir . 'visa.png',
                    'min_sum' => $item['min'],
                    'max_sum' => $item['max'],
                    'bank_alias' => $item['bank_alias']?? '',
                    'test_mode' => true,
                ]
            );

        }

    }
}
