<?php

namespace Database\Seeders;

use App\Models\PaymentSystems;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShadowPaysSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $system_name = 'ShadowPays';
        $image_dir = '/img/paysystem/';

        $items = [
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_KZT,
                'method_alias' => $system_name . '.' . 'p2p_KZT',
                'min' => 1600,
                'max' => 50000,
                'name' => 'shadowpays',
            ],
            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => UserWallet::CURRENCY_KZT,
                'method_alias' => $system_name . '.' . 'p2p_KZT',
                'min' => 1600,
                'max' => 50000,
                'name' => 'shadowpays',
            ],

        ];

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_SHADOWPAYS_PENDING, 'value' => 'STATUS_SHADOWPAYS_PENDING'],
                []
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_SHADOWPAYS_SUCCESS, 'value' => 'STATUS_SHADOWPAYS_SUCCESS'],
                []
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_SHADOWPAYS_FAIL, 'value' => 'STATUS_SHADOWPAYS_FAIL'],
                []
            );

        foreach ($items as $item) {

            PaymentSystems::updateOrCreate(
                [
                    'type' => $item['type'],
                    'currency_id' => $item['currency_id'],
                    'method_alias' => $item['method_alias'],
                    'system_id' => Payin::TYPE_SHADOWSPAYS,
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
