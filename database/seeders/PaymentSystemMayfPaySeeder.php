<?php

namespace Database\Seeders;

use App\Models\PaymentSystems;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentSystemMayfPaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(['id' => PayoutTransaction::STATUS_MAYFPAY_SUCCESS, 'value' => 'STATUS_MAYFPAY_SUCCESS']);
        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(['id' => PayoutTransaction::STATUS_MAYFPAY_FAIL, 'value' => 'STATUS_MAYFPAY_FAIL']);
        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(['id' => PayoutTransaction::STATUS_MAYFPAY_PENDING, 'value' => 'STATUS_MAYFPAY_PENDING']);

        PaymentSystems::updateOrCreate(
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => UserWallet::CURRENCY_RUB,
                'system_id' => Payin::TYPE_MAYFPAY,
                'method_alias' => 'MayfPay.rub',
            ],
            [
                'system_name' => 'MayfPay',
                'name' => 'MayfPay.rub',
                'image_url' => '',
                'min_sum' => 100,
                'max_sum' => 5000,
                'test_mode' => true,
            ]
        );

    }
}
