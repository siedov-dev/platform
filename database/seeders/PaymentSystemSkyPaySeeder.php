<?php

namespace Database\Seeders;

use App\Models\PaymentSystems;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentSystemSkyPaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $system_name = 'SkyPay';
        $name = 'Sky Pay';
        $image_dir = '/img/paysystem/skypay';

        $items = [
            ['type' => PaymentSystems::TYPE_PAYIN, 'currency_id' => UserWallet::CURRENCY_RUB, 'min' => 1000, 'max' => 150000],
            ['type' => PaymentSystems::TYPE_PAYIN, 'currency_id' => UserWallet::CURRENCY_UAH, 'min' => 250, 'max' => 75000],
            ['type' => PaymentSystems::TYPE_PAYIN, 'currency_id' => UserWallet::CURRENCY_USD, 'min' => 8, 'max' => 2200],
            ['type' => PaymentSystems::TYPE_PAYIN, 'currency_id' => UserWallet::CURRENCY_KZT, 'min' => 1000, 'max' => 1000000],

            ['type' => PaymentSystems::TYPE_PAYOUT, 'currency_id' => UserWallet::CURRENCY_RUB, 'min' => 2000, 'max' => 150000],
            ['type' => PaymentSystems::TYPE_PAYOUT, 'currency_id' => UserWallet::CURRENCY_UAH, 'min' => 500, 'max' => 75000],
            ['type' => PaymentSystems::TYPE_PAYOUT, 'currency_id' => UserWallet::CURRENCY_USD, 'min' => 30, 'max' => 2200],
            ['type' => PaymentSystems::TYPE_PAYOUT, 'currency_id' => UserWallet::CURRENCY_KZT, 'min' => 5000, 'max' => 1000000],
        ];

        foreach ($items as $item) {

            switch ($item['currency_id']) {
                case 1:
                case 3:
                    $image_url = 'noname';
                    break;
                case 2:
                    $image_url = 'ua';
                    break;
                case 8:
                    $image_url = 'kz';
                    break;
            }

            PaymentSystems::updateOrCreate(
                [
                    'type' => $item['type'],
                    'currency_id' => $item['currency_id'],
                    'system_id' => Payin::TYPE_SKYPAY,
                    'system_name' => $system_name,
                    'name' => $name,
                ],
                [
                    'method_alias' => $system_name . '.' . $item['currency_id'],
                    'image_url' => $image_dir . '/' . $image_url . '.png',
                    'min_sum' => $item['min'],
                    'max_sum' => $item['max'],
                    'test_mode' => true,
                ]
            );

        }

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_SKYPAY_PENDING, 'value' => 'STATUS_SKYPAY_PENDING'],
                []
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_SKYPAY_SUCCESS, 'value' => 'STATUS_SKYPAY_SUCCESS'],
                []
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_SKYPAY_FAIL, 'value' => 'STATUS_SKYPAY_FAIL'],
                []
            );

    }
}
