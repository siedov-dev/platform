<?php

namespace Database\Seeders;

use App\Models\PaymentSystems;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NirvanaPaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $system_name = 'NirvanaPay';
        $image_dir = '/img/paysystem/';

        $items = [
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'NirvanaPay_RUB',
                'min' => 1000,
                'max' => 30000,
                'name' => 'NirvanaPay RUB',
            ],
            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'Mezhbank_RUB',
                'min' => 1000,
                'max' => 30000,
                'name' => 'Межбанк',
                'bank_alias' => 'Межбанк',
            ],
            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'SBP_RUB',
                'min' => 1000,
                'max' => 30000,
                'name' => 'СБП',
                'bank_alias' => 'СБП',
            ],
            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'TransMezhbank_RUB',
                'min' => 1000,
                'max' => 30000,
                'name' => 'ТрансМежбанк',
                'bank_alias' => 'ТрансМежбанк',
            ],
            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'TransSBP_RUB',
                'min' => 1000,
                'max' => 30000,
                'name' => 'ТрансСБП',
                'bank_alias' => 'ТрансСБП',
            ],
            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'NCPK_RUB',
                'min' => 1000,
                'max' => 30000,
                'name' => 'НСПК',
                'bank_alias' => 'НСПК',
            ],
        ];

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_NIRVANA_PENDING, 'value' => 'STATUS_NIRVANA_PENDING'],
                []
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_NIRVANA_SUCCESS, 'value' => 'STATUS_NIRVANA_SUCCESS'],
                []
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_NIRVANA_FAIL, 'value' => 'STATUS_NIRVANA_FAIL'],
                []
            );

        foreach ($items as $item) {

            PaymentSystems::updateOrCreate(
                [
                    'type' => $item['type'],
                    'currency_id' => $item['currency_id'],
                    'method_alias' => $item['method_alias'],
                    'system_id' => Payin::TYPE_NIRVANA_PAY,
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
