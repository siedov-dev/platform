<?php

namespace Database\Seeders;

use App\Models\PaymentSystems;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EasyBitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $system_name = 'EasyBits';
        $image_dir = '/img/paysystem/';

        $items = [
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_UAH,
                'method_alias' => $system_name . '.' . 'h2h_UAH',
                'min' => 500,
                'max' => 30000,
                'name' => 'UAH',
            ],
            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => UserWallet::CURRENCY_UAH,
                'method_alias' => $system_name . '.' . 'UAH',
                'min' => 500,
                'max' => 30000,
                'name' => 'UAH',
            ],
        ];

        foreach ($items as $item) {

            PaymentSystems::updateOrCreate(
                [
                    'type' => $item['type'],
                    'currency_id' => $item['currency_id'],
                    'method_alias' => $item['method_alias'],
                    'system_id' => Payin::TYPE_EASYBITS,
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
                ['id' => PayoutTransaction::STATUS_EASYBITS_PENDING, 'value' => 'STATUS_EASYBITS_PENDING'],
                []
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_EASYBITS_SUCCESS, 'value' => 'STATUS_EASYBITS_SUCCESS'],
                []
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_EASYBITS_FAIL, 'value' => 'STATUS_EASYBITS_FAIL'],
                []
            );

    }
}
