<?php

namespace Database\Seeders;

use App\Models\PaymentSystems;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PayscrowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $system_name = 'Payscrow';
        $image_dir = '/img/paysystem/';

        $items = [
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . '8fe3669a-a448-4053-bc4b-43bb51cb3e9d',
                'min' => 5000,
                'max' => 300000,
                'name' => 'Любой банк РФ',
            ],
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . '2ec6dbd6-49a5-45d0-bd6d-b0134ee4639a',
                'min' => 5000,
                'max' => 300000,
                'name' => 'СБП',
            ],
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'c154be74-5e4e-4edb-85c2-d2aa175165f5',
                'min' => 5000,
                'max' => 300000,
                'name' => 'Трансграничный СБП',
            ],
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . '560a0a52-3531-44b7-8f7b-5b6dbe5c04e1',
                'min' => 5000,
                'max' => 300000,
                'name' => 'Любой Трансграничный банк',
            ],
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'd3a8d0f7-a8c8-43c7-b77e-61018efc4c4d',
                'min' => 5000,
                'max' => 300000,
                'name' => 'QR РФ',
            ],
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.',
                'min' => 5000,
                'max' => 300000,
                'name' => 'Payscrow',
            ],
            // payout
            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . '8fe3669a-a448-4053-bc4b-43bb51cb3e9d',
                'min' => 5000,
                'max' => 300000,
                'name' => 'Любой банк РФ',
            ],
            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . '2ec6dbd6-49a5-45d0-bd6d-b0134ee4639a',
                'min' => 5000,
                'max' => 300000,
                'name' => 'СБП',
            ],
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
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . '465b838e-1411-4435-a8d5-b9e0b533e9b6',
                'min' => 100,
                'max' => 300000,
                'name' => 'Счёт Сбербанк',
            ],
        ];

        foreach ($items as $item) {

            PaymentSystems::updateOrCreate(
                [
                    'type' => $item['type'],
                    'currency_id' => $item['currency_id'],
                    'method_alias' => $item['method_alias'],
                    'system_id' => Payin::TYPE_PAYSCROW,
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
                ['id' => PayoutTransaction::STATUS_PAYSCROW_PENDING, 'value' => 'STATUS_PAYSCROW_PENDING'],
                []
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_PAYSCROW_SUCCESS, 'value' => 'STATUS_PAYSCROW_SUCCESS'],
                []
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_PAYSCROW_FAIL, 'value' => 'STATUS_PAYSCROW_FAIL'],
                []
            );

    }
}
