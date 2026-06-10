<?php

namespace Database\Seeders;

use App\Models\PaymentMethodData;
use App\Models\PaymentSystems;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OneVisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $system_name = 'OneVision';
        $image_dir = '/img/paysystem/';

        // ecom
        $items = [
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_UZS,
                'method_alias' => $system_name . '.' . 'UZS_ecom_ftd',
                'min' => 20006,
                'max' => 300000,
                'name' => 'Ecom ftd',
                'data' => [
                    'API' => '210d0681-fcce-4fad-9040-8ca9978991a4',
                    'SECRET' => '7cb101d599f243c26430fc9427c96b97062976afb7a831478c30cc131569a696',
                    'SID' => '24c22cc7-778b-4206-b691-6a7e643ef4b1',
                    'MID' => '0e523b2a-e07a-4f34-9d6f-40b0d79f516e',
                ],
            ],
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_UZS,
                'method_alias' => $system_name . '.' . 'UZS_ecom_std',
                'min' => 20006,
                'max' => 300000,
                'name' => 'Ecom std',
                'data' => [
                    'API' => '51978e1f-f9a9-4a1e-8270-b941591e2757',
                    'SECRET' => '63d172f5763c0953e85185161f9a839848cdd98b958949484b5ecd5ab4f8072c',
                    'SID' => '24c22cc7-778b-4206-b691-6a7e643ef4b1',
                    'MID' => '90b981d9-8b2c-4147-aa9b-9801e3598aec',
                ],
            ],
            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => UserWallet::CURRENCY_UZS,
                'method_alias' => $system_name . '.' . 'UZS_ecom',
                'min' => 20006,
                'max' => 300000,
                'name' => 'Ecom',
                'data' => [
                    'API' => 'b38f5d4f-85dc-4ccb-b899-315d8f93caf4',
                    'SECRET' => '1a97a7efad46daf87da443395cfaf6493a3a9d3955231734da73fba6aba4612e',
                    'SID' => '24c22cc7-778b-4206-b691-6a7e643ef4b1',
                    'MID' => '46d8d514-d6ea-436e-881c-abc1f9c9c856',
                ],
            ],
            // p2p
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_UZS,
                'method_alias' => $system_name . '.' . 'UZS_ecom_ftd_p2p',
                'min' => 20006,
                'max' => 300000,
                'name' => 'p2p ftd',
                'data' => [
                    'API' => '53c84f35-e636-41ab-9810-2c9f07db76ce',
                    'SECRET' => '5168d96cdd8a13fece60d5b55ea6d50b3a767b3a9450cc7090ee4bdaca9f1028',
                    'SID' => '24c22cc7-778b-4206-b691-6a7e643ef4b1',
                    'MID' => '04f8456c-c85c-467f-a3be-91306ccaa996',
                ],
            ],
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_UZS,
                'method_alias' => $system_name . '.' . 'UZS_ecom_std_p2p',
                'min' => 20006,
                'max' => 300000,
                'name' => 'p2p std',
                'data' => [
                    'API' => '2f993006-66e8-45f6-92bf-588570c5ca41',
                    'SECRET' => '4954ac3a2557706dd5bb02b06c3e4905e75c0949244f17ae85777b2f1d181cb0',
                    'SID' => '24c22cc7-778b-4206-b691-6a7e643ef4b1',
                    'MID' => '41708d22-010a-44e3-a414-d97241a70d49',
                ],
            ],
            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => UserWallet::CURRENCY_UZS,
                'method_alias' => $system_name . '.' . 'UZS_ecom_p2p',
                'min' => 20006,
                'max' => 300000,
                'name' => 'p2p',
                'data' => [
                    'API' => '61c6cd57-2eba-4380-b5fb-31ce3cdbd9eb',
                    'SECRET' => 'c8ac94ff53e56fd25438cdb6e9eb381116da69fff5516b6a4d91508a5a247e0e',
                    'SID' => '24c22cc7-778b-4206-b691-6a7e643ef4b1',
                    'MID' => 'af66fdc2-ccb7-4ff7-8268-093cd543899e',
                ],
            ],
            // kzt

            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_KZT,
                'method_alias' => $system_name . '.' . 'KZT_ecom_ftd',
                'min' => 20006,
                'max' => 300000,
                'name' => 'Ecom ftd',
                'data' => [
                    'API' => '6064fdc0-2eeb-4af4-97fc-f10ffe6f896f',
                    'SECRET' => '6f2fc6609ce02ab98480195eabc5ba0a929c6102fbea96bf2eae04a3b85e2ee4',
                    'SID' => '24c22cc7-778b-4206-b691-6a7e643ef4b1',
                    'MID' => '54803ea3-0cd9-47c0-a3ee-7222d96d0bf5',
                ],
            ],
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_KZT,
                'method_alias' => $system_name . '.' . 'KZT_ecom_std',
                'min' => 20006,
                'max' => 300000,
                'name' => 'Ecom std',
                'data' => [
                    'API' => 'b5f731bf-190b-4d78-81c2-be8e8cc12b69',
                    'SECRET' => '4415707d1d5adf9154076c809976ed35cc8415825c5eafd9c317ff56232a1443',
                    'SID' => '24c22cc7-778b-4206-b691-6a7e643ef4b1',
                    'MID' => '8a333ac2-df48-45f8-bac7-ad8cb6a47aa2',
                ],
            ],
            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => UserWallet::CURRENCY_KZT,
                'method_alias' => $system_name . '.' . 'KZT_ecom',
                'min' => 20006,
                'max' => 300000,
                'name' => 'Ecom',
                'data' => [
                    'API' => '8b21f170-c4b9-4f91-b889-de2e94f99c82',
                    'SECRET' => 'c222f1ad82817d58f3da6ca4f8de558fb57121aa6031b86bb80fcc4f6b93555d',
                    'SID' => '24c22cc7-778b-4206-b691-6a7e643ef4b1',
                    'MID' => '6e63d5a7-af4f-4c3a-b982-56bbf4b1d181',
                ],
            ],

            // kzt mc
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_KZT,
                'method_alias' => $system_name . '.' . 'KZT_mc_ftd',
                'min' => 20006,
                'max' => 300000,
                'name' => 'MC ftd',
                'data' => [
                    'API' => '52b101b9-eca8-493b-9d0d-9009a16a5b6d',
                    'SECRET' => '1b4e1d8ccdcb8260b31abb4ac38b018331bb25e1791cbf19161d570d8a9e0327',
                    'SID' => '24c22cc7-778b-4206-b691-6a7e643ef4b1',
                    'MID' => '13366331-e0d9-4ad6-a77f-ffb1367ba8d9',
                ],
            ],
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_KZT,
                'method_alias' => $system_name . '.' . 'KZT_mc_std',
                'min' => 20006,
                'max' => 300000,
                'name' => 'MC std',
                'data' => [
                    'API' => 'af780c6d-ecf2-4d1e-bad1-e36d5f158c16',
                    'SECRET' => 'cdaa93d0484afb045c910667af1c1c7658e8dd71e56a0f3b70e3cbb5a34da609',
                    'SID' => '24c22cc7-778b-4206-b691-6a7e643ef4b1',
                    'MID' => '7d06213c-aee3-41f7-a7f8-6cf15a25c3d5',
                ],
            ],
            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => UserWallet::CURRENCY_KZT,
                'method_alias' => $system_name . '.' . 'KZT_mc',
                'min' => 20006,
                'max' => 300000,
                'name' => 'MC',
                'data' => [
                    'API' => 'ddedab3d-0027-47d3-b722-dabc9b175551',
                    'SECRET' => '572a2c827c1c3399379d2365354159bb7682828f22c3ac75a69f5208d7e62690',
                    'SID' => '24c22cc7-778b-4206-b691-6a7e643ef4b1',
                    'MID' => '6598faa6-0235-4ac8-9e81-9a921272caff',
                ],
            ],

        ];

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_ONEVISION_PENDING, 'value' => 'STATUS_ONEVISION_PENDING'],
                []
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_ONEVISION_SUCCESS, 'value' => 'STATUS_ONEVISION_SUCCESS'],
                []
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_ONEVISION_FAIL, 'value' => 'STATUS_ONEVISION_FAIL'],
                []
            );

        foreach ($items as $item) {

            $method = PaymentSystems::updateOrCreate(
                [
                    'type' => $item['type'],
                    'currency_id' => $item['currency_id'],
                    'method_alias' => $item['method_alias'],
                    'system_id' => Payin::TYPE_ONEVISION,
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
