<?php

namespace Database\Seeders;

use App\Models\PaymentMethodData;
use App\Models\PaymentSystems;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SequoiaPaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $system_name = 'SequoiaPay';
        $image_dir = '/img/paysystem/';

        $items = [
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_KZT,
                'method_alias' => $system_name . '.' . 'KZT:p2p',
                'min' => 10007,
                'max' => 40000,
                'name' => 'p2p',
                'data' => [
                    'Secret' => 'E0hZomvGP1f4lKFv67R0HIto',
                    'Webhook' => 'PvwpqoPPRnGtujDf0JzntoJL',
                ],
            ],
            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => UserWallet::CURRENCY_KZT,
                'method_alias' => $system_name . '.' . 'KZT:p2p',
                'min' => 10007,
                'max' => 40000,
                'name' => 'p2p',
                'data' => [
                    'Secret' => 'E0hZomvGP1f4lKFv67R0HIto',
                    'Webhook' => 'PvwpqoPPRnGtujDf0JzntoJL',
                ],
            ],
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'RUB:p2p',
                'min' => 1007,
                'max' => 40000,
                'name' => 'p2p',
                'data' => [
                    'Secret' => 'ODMKx0UF9dDuecgidYEFpU7i',
                    'Webhook' => '5stLxZLvUiCunZ99NdcyMd0r',
                ],
            ],
            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'RUB:p2p',
                'min' => 1007,
                'max' => 40000,
                'name' => 'p2p',
                'data' => [
                    'Secret' => 'ODMKx0UF9dDuecgidYEFpU7i',
                    'Webhook' => '5stLxZLvUiCunZ99NdcyMd0r',
                ],
            ],
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'RUB:sbp',
                'min' => 1008,
                'max' => 40001,
                'name' => 'sbp',
                'data' => [
                    'Secret' => 'rEh6uvPiEGUU6hyhAVyAb7fZ',
                    'Webhook' => '38UMzaR8cKzptkcFIHPUvVVD',
                ],
            ],
            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'RUB:sbp',
                'min' => 1008,
                'max' => 40001,
                'name' => 'sbp',
                'data' => [
                    'Secret' => 'rEh6uvPiEGUU6hyhAVyAb7fZ',
                    'Webhook' => '38UMzaR8cKzptkcFIHPUvVVD',
                ],
            ],
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'RUB:qr',
                'min' => 1009,
                'max' => 40002,
                'name' => 'qr',
                'data' => [
                    'Secret' => 'Sqx0C2bGatdpetDktPbj4fy7',
                    'Webhook' => 'hAqGUJSMcK1TQJI2RlMHyHdj',
                ],
            ],
        ];

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_SEQUOIA_PENDING, 'value' => 'SEQUOIA_PENDING'],
                []
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_SEQUOIA_SUCCESS, 'value' => 'SEQUOIA_SUCCESS'],
                []
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_SEQUOIA_FAIL, 'value' => 'SEQUOIA_FAIL'],
                []
            );


        // create/update methods
        foreach ($items as $item) {

            $method = PaymentSystems::updateOrCreate(
                [
                    'type' => $item['type'],
                    'currency_id' => $item['currency_id'],
                    'method_alias' => $item['method_alias'],
                    'system_id' => Payin::TYPE_SEQUOIAPAY,
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
