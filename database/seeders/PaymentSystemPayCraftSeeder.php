<?php

namespace Database\Seeders;

use App\Models\PaymentSystems;

use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentSystemPayCraftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $system_name = 'PayCraft';
        $name = 'Pay Craft';
        $image_path = '/img/paysystem/paycraft.jpg';

        $items = [
            ['type' => PaymentSystems::TYPE_PAYIN, 'min' => 500, 'max' => 100000], //🫡
            ['type' => PaymentSystems::TYPE_PAYOUT, 'min' => 3000, 'max' => 300000], //🫡
        ];

        foreach ($items as $item) {

            PaymentSystems::updateOrCreate(
                [
                    'type' => $item['type'],
                    'currency_id' => UserWallet::CURRENCY_RUB,
                    'system_id' => Payin::TYPE_PAYCRAFT,
                    'system_name' => $system_name,
                    'name' => $name,
                ],
                [
                    'method_alias' => $system_name . '.' . UserWallet::CURRENCY_RUB,
                    'image_url' => $image_path,
                    'min_sum' => $item['min'],
                    'max_sum' => $item['max'],
                    'test_mode' => true,
                ]
            );

        }

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_PAYCRAFT_PENDING, 'value' => 'STATUS_PAYCRAFT_PENDING'],
                []
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_PAYCRAFT_SUCCESS, 'value' => 'STATUS_PAYCRAFT_SUCCESS'],
                []
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_PAYCRAFT_FAIL, 'value' => 'STATUS_PAYCRAFT_FAIL'],
                []
            );

    }
}
