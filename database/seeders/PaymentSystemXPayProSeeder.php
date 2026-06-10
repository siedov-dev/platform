<?php

namespace Database\Seeders;

use App\Models\PaymentSystems;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentSystemXPayProSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $system_name = 'XPayPro';
        $name = 'XPayPro';
        $image_dir = '/img/paysystem/';

        $items = [
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'C2C',
                'min' => 1000,
                'max' => 300000,
                'name' => $name,
            ],
            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'BANK_CARD',
                'min' => 1300,
                'max' => 50000,
                'name' => "Сбербанк",
            ],
            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'SBP',
                'min' => 1400,
                'max' => 50000,
                'name' => "СБП",
            ],

        ];

        foreach ($items as $item) {

            PaymentSystems::updateOrCreate(
                [
                    'type' => $item['type'],
                    'currency_id' => $item['currency_id'],
                    'method_alias' => $item['method_alias'],
                    'system_id' => Payin::TYPE_XPAYPRO,
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
                ['id' => PayoutTransaction::STATUS_XPAYPRO_PENDING, 'value' => 'STATUS_XPAYPRO_PENDING'],
                []
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_XPAYPRO_SUCCESS, 'value' => 'STATUS_XPAYPRO_SUCCESS'],
                []
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_XPAYPRO_FAIL, 'value' => 'STATUS_XPAYPRO_FAIL'],
                []
            );

    }
}
