<?php

namespace Database\Seeders;

use App\Models\PaymentSystems;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BitZonePaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $system_name = 'BitZone';
        $image_dir = '/img/paysystem/';

        $items = [
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => Wallet::CURRENCY_UAH,
                'method_alias' => $system_name . '.' . 'all',
                'min' => 500,
                'max' => 300000,
                'name' => 'Paychain',
            ],

            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => Wallet::CURRENCY_UAH,
                'method_alias' => $system_name . '.' . 'card',
                'min' => 500,
                'max' => 300000,
                'name' => 'Paychain',
            ],

        ];

        foreach ($items as $item) {

            PaymentSystems::updateOrCreate(
                [
                    'type' => $item['type'],
                    'currency_id' => $item['currency_id'],
                    'method_alias' => $item['method_alias'],
                    'system_id' => Payin::TYPE_BITZONE,
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
                ['id' => PayoutTransaction::STATUS_BITZONE_PENDING, 'value' => 'STATUS_BITZONE_PENDING'],
                []
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_BITZONE_SUCCESS, 'value' => 'STATUS_BITZONE_SUCCESS'],
                []
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_BITZONE_FAIL, 'value' => 'STATUS_BITZONE_FAIL'],
                []
            );
    }
}
