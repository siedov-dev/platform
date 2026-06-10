<?php

namespace Database\Seeders;

use App\Models\PaymentSystems;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InfinityPaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $system_name = 'InfinityPay';
        $image_dir = '/img/paysystem/';

        $items = [
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'CIS_RUB',
                'min' => 1005,
                'max' => 30000,
                'name' => 'InfinityPay RUB',
            ],
            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'card_RUB',
                'min' => 1005,
                'max' => 30000,
                'name' => 'InfinityPay card RUB',
            ],
            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'phone_RUB',
                'min' => 1006,
                'max' => 30000,
                'name' => 'InfinityPay phone RUB',
            ],
        ];

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_INFINITY_PENDING, 'value' => 'STATUS_INFINITY_PENDING'],
                []
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_INFINITY_SUCCESS, 'value' => 'STATUS_INFINITY_SUCCESS'],
                []
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_INFINITY_FAIL, 'value' => 'STATUS_INFINITY_FAIL'],
                []
            );

        foreach ($items as $item) {

            PaymentSystems::updateOrCreate(
                [
                    'type' => $item['type'],
                    'currency_id' => $item['currency_id'],
                    'method_alias' => $item['method_alias'],
                    'system_id' => Payin::TYPE_INFINITY_PAY,
                    'system_name' => $system_name,
                    'name' => $item['name'],
                ],
                [
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
