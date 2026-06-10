<?php

namespace Database\Seeders;

use App\Models\PaymentMethodData;
use App\Models\PaymentSystems;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PspyPaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $system_name = 'Pspy';
        $image_dir = '/img/paysystem/';

        $items = [
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_KZT,
                'method_alias' => $system_name . '.' . 'all',
                'min' => 11018,
                'max' => 30001,
                'name' => 'Pspy',
                'data' => [
                ],
            ],
            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => UserWallet::CURRENCY_KZT,
                'method_alias' => $system_name . '.' . 'card',
                'min' => 11018,
                'max' => 30001,
                'name' => 'Pspy Card',
                'data' => [
                ],
            ],
        ];

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_PSPY_PENDING, 'value' => 'PSPY_PENDING'],
                []
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_PSPY_SUCCESS, 'value' => 'PSPY_SUCCESS'],
                []
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_PSPY_FAIL, 'value' => 'PSPY_FAIL'],
                []
            );


        // create/update methods
        foreach ($items as $item) {

            $method = PaymentSystems::updateOrCreate(
                [
                    'type' => $item['type'],
                    'currency_id' => $item['currency_id'],
                    'method_alias' => $item['method_alias'],
                    'system_id' => Payin::TYPE_PSPY,
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
