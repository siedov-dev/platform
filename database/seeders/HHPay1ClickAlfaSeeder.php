<?php

namespace Database\Seeders;

use App\Models\PaymentMethodData;
use App\Models\PaymentSystems;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HHPay1ClickAlfaSeeder extends Seeder
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
                'method_alias' => $system_name . '.' . 'payin-1-click:alfa-bank',
                'min' => 1,
                'max' => 30000,
                'name' => 'alfa-bank',
                'bank_alias' => 'alfa-bank',
                'data' => [
                    'project_id' => 'a8f5ec40-7a78-4fde-8fd8-578172fefe3d',
                ],
            ],
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'payin-p2p-mobile:1-click',
                'min' => 1,
                'max' => 30000,
                'name' => 'p2p mobile',
                'bank_alias' => '',
                'data' => [
                    'project_id' => 'a8f5ec40-7a78-4fde-8fd8-578172fefe3d',
                ],
            ],

        ];

        // set project key for ol methods
        foreach (PaymentSystems::where('system_id', Payin::TYPE_HIGHHELP)->get() as $old_methods) {
            PaymentMethodData::updateOrCreate(
                [
                    'payment_method_id' => $old_methods->id,
                    'key' => "project_id",
                ],
                ['value' => "11c67047-be61-4a88-861a-6b899f815cfe"]
            );
        }

        // create/update methods
        foreach ($items as $item) {

            $method = PaymentSystems::updateOrCreate(
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
