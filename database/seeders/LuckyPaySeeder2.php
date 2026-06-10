<?php

namespace Database\Seeders;

use App\Models\PaymentSystems;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LuckyPaySeeder2 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $system_name = 'LuckyPay';
        $image_dir = '/img/paysystem/';

        $items = [
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . '2:8fe3669a-a448-4053-bc4b-43bb51cb3e9d',
                'min' => 100,
                'max' => 300000,
                'name' => 'Любой банк РФ (100-999)',
            ],
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . '2:2ec6dbd6-49a5-45d0-bd6d-b0134ee4639a',
                'min' => 100,
                'max' => 300000,
                'name' => 'СБП  (100-999)',
            ],

        ];

        foreach ($items as $item) {

            PaymentSystems::updateOrCreate(
                [
                    'type' => $item['type'],
                    'currency_id' => $item['currency_id'],
                    'method_alias' => $item['method_alias'],
                    'system_id' => Payin::TYPE_LUCKYPAY,
                    'system_name' => $system_name,
                    'name' => $item['name'],
                ],
                [
                    'image_url' => $image_dir . 'visa.png',
                    'min_sum' => $item['min'],
                    'max_sum' => $item['max'],
                    'test_mode' => true,
                ]
            );

        }

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_LUCKYPAY_PENDING, 'value' => 'STATUS_LUCKYPAY_PENDING'],
                []
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_LUCKYPAY_SUCCESS, 'value' => 'STATUS_LUCKYPAY_SUCCESS'],
                []
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_LUCKYPAY_FAIL, 'value' => 'STATUS_LUCKYPAY_FAIL'],
                []
            );

    }
}
