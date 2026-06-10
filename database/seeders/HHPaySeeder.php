<?php

namespace Database\Seeders;

use App\Models\PaymentSystems;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HHPaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $system_name = 'HighHelp';
        $image_dir = '/img/paysystem/';

        $items = [
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'payin-ecom-card',
                'min' => 1,
                'max' => 30000,
                'name' => 'Ecom',
            ],
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_KZT,
                'method_alias' => $system_name . '.' . 'payin-ecom-card',
                'min' => 10000,
                'max' => 300000,
                'name' => 'Ecom',
            ],
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_UZS,
                'method_alias' => $system_name . '.' . 'payin-ecom-card',
                'min' => 10000,
                'max' => 300000,
                'name' => 'Ecom',
            ],
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'payin-p2p-card',
                'min' => 1,
                'max' => 30000,
                'name' => 'Card',
            ],
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_UZS,
                'method_alias' => $system_name . '.' . 'payin-p2p-card',
                'min' => 10000,
                'max' => 300000,
                'name' => 'Card',
            ],
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'payin-p2p-sbp',
                'min' => 1,
                'max' => 30000,
                'name' => 'Sbp',
            ],
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'payin-p2p-international',
                'min' => 1,
                'max' => 30000,
                'name' => 'p2p international',
            ],
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'payin-p2p-mobile',
                'min' => 1,
                'max' => 30000,
                'name' => 'p2p mobile',
            ],
            //payout
            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'card-ecom',
                'min' => 1,
                'max' => 30000,
                'name' => 'Ecom',
            ],
            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'card-p2p',
                'min' => 1,
                'max' => 30000,
                'name' => 'Card p2p',
            ],
            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'card-sbp',
                'min' => 1,
                'max' => 30000,
                'name' => 'Sbp',
            ],

        ];

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_HIGHHELP_PENDING, 'value' => 'STATUS_HIGHHELP_PENDING'],
                []
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_HIGHHELP_SUCCESS, 'value' => 'STATUS_HIGHHELP_SUCCESS'],
                []
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_HIGHHELP_FAIL, 'value' => 'STATUS_HIGHHELP_FAIL'],
                []
            );

        foreach ($items as $item) {

            PaymentSystems::updateOrCreate(
                [
                    'type' => $item['type'],
                    'currency_id' => $item['currency_id'],
                    'method_alias' => $item['method_alias'],
                    'system_id' => Payin::TYPE_HIGHHELP,
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
