<?php

namespace Database\Seeders;

use App\Models\PaymentMethodData;
use App\Models\PaymentSystems;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SarexPaysSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $system_name = 'SarexPays';
        $image_dir = '/img/paysystem/';

        $items = [
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_KZT,
                'method_alias' => $system_name . '.' . 'mob_kz:Beeline',
                'min' => 10018,
                'max' => 40001,
                'name' => 'Beeline',
                'data' => [
                ],
            ],
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_KZT,
                'method_alias' => $system_name . '.' . 'mob_kz:Kcell',
                'min' => 10019,
                'max' => 40002,
                'name' => 'Kcell',
                'data' => [
                ],
            ],
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_KZT,
                'method_alias' => $system_name . '.' . 'mob_kz:Active',
                'min' => 10020,
                'max' => 40003,
                'name' => 'Active',
                'data' => [
                ],
            ],
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_KZT,
                'method_alias' => $system_name . '.' . 'mob_kz:Tele2',
                'min' => 10021,
                'max' => 40004,
                'name' => 'Tele2',
                'data' => [
                ],
            ],
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_KZT,
                'method_alias' => $system_name . '.' . 'mob_kz:Altel',
                'min' => 10022,
                'max' => 40005,
                'name' => 'Altel',
                'data' => [
                ],
            ],
            //payout
            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => UserWallet::CURRENCY_KZT,
                'method_alias' => $system_name . '.' . 'mob_kz:Beeline',
                'min' => 10018,
                'max' => 40001,
                'name' => 'Beeline',
                'data' => [
                ],
            ],
            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => UserWallet::CURRENCY_KZT,
                'method_alias' => $system_name . '.' . 'mob_kz:Kcell',
                'min' => 10019,
                'max' => 40002,
                'name' => 'Kcell',
                'data' => [
                ],
            ],
            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => UserWallet::CURRENCY_KZT,
                'method_alias' => $system_name . '.' . 'mob_kz:Active',
                'min' => 10020,
                'max' => 40003,
                'name' => 'Active',
                'data' => [
                ],
            ],
            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => UserWallet::CURRENCY_KZT,
                'method_alias' => $system_name . '.' . 'mob_kz:Tele2',
                'min' => 10021,
                'max' => 40004,
                'name' => 'Tele2',
                'data' => [
                ],
            ],
            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => UserWallet::CURRENCY_KZT,
                'method_alias' => $system_name . '.' . 'mob_kz:Altel',
                'min' => 10022,
                'max' => 40005,
                'name' => 'Altel',
                'data' => [
                ],
            ],
        ];

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_SAREX_PENDING, 'value' => 'SAREX_PENDING'],
                []
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_SAREX_SUCCESS, 'value' => 'SAREX_SUCCESS'],
                []
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_SAREX_FAIL, 'value' => 'SAREX_FAIL'],
                []
            );


        // create/update methods
        foreach ($items as $item) {

            $method = PaymentSystems::updateOrCreate(
                [
                    'type' => $item['type'],
                    'currency_id' => $item['currency_id'],
                    'method_alias' => $item['method_alias'],
                    'system_id' => Payin::TYPE_SAREX,
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

            if (!empty($item['data'])) {
                foreach ($item['data'] as $key => $value) {
                    PaymentMethodData::updateOrCreate(
                        [
                            'payment_method_id' => $method->id,
                            'key' => $key
                        ],
                        ['value' => $value]
                    );
                }
            }
        }

    }
}
