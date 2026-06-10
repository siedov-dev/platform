<?php

namespace Database\Seeders;

use App\Models\PaymentSystems;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BetaTransferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $system_name = 'Betatransfer';
        $image_dir = '/img/paysystem/';

        $items = [
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => Wallet::CURRENCY_KZT,
                'method_alias' => $system_name . '.' . 'Card2',
                'min' => 100,
                'max' => 300000,
                'name' => 'Visa/Mastercard KZT Ecom TD',
            ],
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => Wallet::CURRENCY_KZT,
                'method_alias' => $system_name . '.' . 'Card3',
                'min' => 100,
                'max' => 300000,
                'name' => 'Visa/Mastercard KZT Ecom FTD',
            ],

        ];

        foreach ($items as $item) {

            PaymentSystems::updateOrCreate(
                [
                    'type' => $item['type'],
                    'currency_id' => $item['currency_id'],
                    'method_alias' => $item['method_alias'],
                    'system_id' => Payin::TYPE_BETATRANSFER,
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
    }
}
