<?php

namespace Database\Seeders;

use App\Models\PaymentSystems;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymePaySeeder extends Seeder
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
                'method_alias' => $system_name . '.' . 'RUB',
                'min' => 600,
                'max' => 300000,
                'name' => 'Payme',
            ],

            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => Wallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'RUB_card',
                'min' => 600,
                'max' => 300000,
                'name' => 'Payme card',
            ],

            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => Wallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'RUB_phone',
                'min' => 600,
                'max' => 300000,
                'name' => 'Payme phone',
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
                ['id' => PayoutTransaction::STATUS_PAYME_PENDING, 'value' => 'STATUS_PAYME_PENDING'],
                []
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_PAYME_SUCCESS, 'value' => 'STATUS_PAYME_SUCCESS'],
                []
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_PAYME_FAIL, 'value' => 'STATUS_PAYME_FAIL'],
                []
            );
    }
}
