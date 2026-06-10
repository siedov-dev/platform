<?php

namespace Database\Seeders;

use App\Models\PaymentSystemGroup;
use App\Models\PaymentSystems;

use App\Payin\Payin;
use App\Wallet;
use Illuminate\Database\Seeder;

class ExPayUzsCryptoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $system_name = 'ExPay';

        $c_all = [Wallet::CURRENCY_UZS];

        $items = [
            PaymentSystems::TYPE_PAYIN => [
                ['i' => 'bitcoin.svg', 'm' => 'BTC', 'n' => 'BTC', 'c' => $c_all, 'min' => 10000, 'max' => 250000],
                ['i' => 'ethereum.svg', 'm' => 'ETH', 'n' => 'ETH', 'c' => $c_all, 'min' => 10000, 'max' => 250000],
                ['i' => 'tron.svg', 'm' => 'TRX', 'n' => 'TRX', 'c' => $c_all, 'min' => 5000, 'max' => 100000],
                ['i' => 'ton.svg', 'm' => 'TON', 'n' => 'TON', 'c' => $c_all, 'min' => 5000, 'max' => 100000],
                ['i' => 'doge.svg', 'm' => 'DOGE', 'n' => 'DOGE', 'c' => $c_all, 'min' => 5000, 'max' => 100000],
                ['i' => 'bch.svg', 'm' => 'BCH', 'n' => 'BCH', 'c' => $c_all, 'min' => 5000, 'max' => 100000],
                ['i' => 'xrp.svg', 'm' => 'XRP', 'n' => 'XRP', 'c' => $c_all, 'min' => 2500, 'max' => 200000],
                ['i' => 'ltc.svg', 'm' => 'LTC', 'n' => 'LTC', 'c' => $c_all, 'min' => 2500, 'max' => 200000],
                ['i' => 'usdt_bsc.svg', 'm' => 'USDTBSC', 'n' => 'USDT BEP20', 'c' => $c_all, 'min' => 2500, 'max' => 200000],
                ['i' => 'usdt_tron.png', 'm' => 'USDTTRC', 'n' => 'USDT TRC20', 'c' => $c_all, 'min' => 2500, 'max' => 200000],
                ['i' => 'usdt_erc.svg', 'm' => 'USDTERC', 'n' => 'USDT ERC20', 'c' => $c_all, 'min' => 2500, 'max' => 200000],

                ['i' => 'usdtton.svg', 'm' => 'USDTTON', 'n' => 'USDT TON', 'c' => $c_all, 'min' => 2500, 'max' => 200000],
                ['i' => 'usdtpoly.svg', 'm' => 'USDTPOLY', 'n' => 'USDT POLY', 'c' => $c_all, 'min' => 2500, 'max' => 200000],
                ['i' => 'matic.svg', 'm' => 'MATIC', 'n' => 'MATIC', 'c' => $c_all, 'min' => 2500, 'max' => 200000],
                ['i' => 'dogebsc.svg', 'm' => 'DOGEBSC', 'n' => 'DOGE BSC', 'c' => $c_all, 'min' => 2500, 'max' => 200000],
                ['i' => 'shibbsc.svg', 'm' => 'SHIBBSC', 'n' => 'SHIB BSC', 'c' => $c_all, 'min' => 2500, 'max' => 200000],
                ['i' => 'usdcpoly.svg', 'm' => 'USDCPOLY', 'n' => 'USDC POLY', 'c' => $c_all, 'min' => 2500, 'max' => 200000],
                ['i' => 'dash.svg', 'm' => 'DASH', 'n' => 'DASH', 'c' => $c_all, 'min' => 2500, 'max' => 200000],
                ['i' => 'daierc.svg', 'm' => 'DAIERC', 'n' => 'DAI ERC', 'c' => $c_all, 'min' => 2500, 'max' => 200000],
                ['i' => 'daipoly.svg', 'm' => 'DAIPOLY', 'n' => 'DAI POLY', 'c' => $c_all, 'min' => 2500, 'max' => 200000],
                ['i' => 'btcbbsc.svg', 'm' => 'BTCBBSC', 'n' => 'BTCB BSC', 'c' => $c_all, 'min' => 2500, 'max' => 200000],
                ['i' => 'usdcerc.svg', 'm' => 'USDCERC', 'n' => 'USDC ERC', 'c' => $c_all, 'min' => 2500, 'max' => 200000],
                ['i' => 'avax.svg', 'm' => 'AVAX', 'n' => 'AVAX', 'c' => $c_all, 'min' => 2500, 'max' => 200000],
                ['i' => 'bnb.svg', 'm' => 'BNB', 'n' => 'BNB', 'c' => $c_all, 'min' => 2500, 'max' => 200000],
                ['i' => 'not.svg', 'm' => 'NOT', 'n' => 'NOT', 'c' => $c_all, 'min' => 2500, 'max' => 200000],
            ],

            PaymentSystems::TYPE_PAYOUT => [
                ['i' => 'usdt_tron.png', 'm' => 'USDTTRC', 'n' => 'USDT TRC20', 'c' => $c_all, 'min' => 10000, 'max' => 200000, 'g' => PaymentSystemGroup::Crypto],
                ['i' => 'bitcoin.svg', 'm' => 'BTC', 'n' => 'BTC', 'c' => $c_all, 'min' => 10000, 'max' => 250000, 'g' => PaymentSystemGroup::Crypto],
                ['i' => 'ethereum.svg', 'm' => 'ETH', 'n' => 'ETH', 'c' => $c_all, 'min' => 10000, 'max' => 250000, 'g' => PaymentSystemGroup::Crypto],
                ['i' => 'tron.svg', 'm' => 'TRX', 'n' => 'TRX', 'c' => $c_all, 'min' => 5000, 'max' => 100000, 'g' => PaymentSystemGroup::Crypto],
                ['i' => 'ton.svg', 'm' => 'TON', 'n' => 'TON', 'c' => $c_all, 'min' => 5000, 'max' => 100000, 'g' => PaymentSystemGroup::Crypto],
                ['i' => 'doge.svg', 'm' => 'DOGE', 'n' => 'DOGE', 'c' => $c_all, 'min' => 5000, 'max' => 100000, 'g' => PaymentSystemGroup::Crypto],
                ['i' => 'bch.svg', 'm' => 'BCH', 'n' => 'BCH', 'c' => $c_all, 'min' => 5000, 'max' => 100000, 'g' => PaymentSystemGroup::Crypto],
                //['i' => 'xrp.svg', 'm' => 'XRP', 'n' => 'XRP', 'c' => $ccc_all, 'min' => 250, 'max' => 200000, 'g' => PaymentSystemGroup::Crypto],
                ['i' => 'ltc.svg', 'm' => 'LTC', 'n' => 'LTC', 'c' => $c_all, 'min' => 2500, 'max' => 200000, 'g' => PaymentSystemGroup::Crypto],
                ['i' => 'usdt_bsc.svg', 'm' => 'USDTBSC', 'n' => 'USDT BEP20', 'c' => $c_all, 'min' => 2500, 'max' => 200000, 'g' => PaymentSystemGroup::Crypto],
                ['i' => 'usdt_tron.png', 'm' => 'USDTTRC', 'n' => 'USDT TRC20', 'c' => $c_all, 'min' => 2500, 'max' => 200000, 'g' => PaymentSystemGroup::Crypto],
                ['i' => 'usdt_erc.svg', 'm' => 'USDTERC', 'n' => 'USDT ERC20', 'c' => $c_all, 'min' => 2500, 'max' => 200000, 'g' => PaymentSystemGroup::Crypto],

                ['i' => 'usdtton.svg', 'm' => 'USDTTON', 'n' => 'USDT TON', 'c' => $c_all, 'min' => 2500, 'max' => 200000],
                ['i' => 'usdtpoly.svg', 'm' => 'USDTPOLY', 'n' => 'USDT POLY', 'c' => $c_all, 'min' => 2500, 'max' => 200000],
                ['i' => 'matic.svg', 'm' => 'MATIC', 'n' => 'MATIC', 'c' => $c_all, 'min' => 2500, 'max' => 200000],
                ['i' => 'dogebsc.svg', 'm' => 'DOGEBSC', 'n' => 'DOGE BSC', 'c' => $c_all, 'min' => 2500, 'max' => 200000],
                ['i' => 'shibbsc.svg', 'm' => 'SHIBBSC', 'n' => 'SHIB BSC', 'c' => $c_all, 'min' => 2500, 'max' => 200000],
                ['i' => 'usdcpoly.svg', 'm' => 'USDCPOLY', 'n' => 'USDC POLY', 'c' => $c_all, 'min' => 2500, 'max' => 200000],
                ['i' => 'dash.svg', 'm' => 'DASH', 'n' => 'DASH', 'c' => $c_all, 'min' => 2500, 'max' => 200000],
                ['i' => 'daierc.svg', 'm' => 'DAIERC', 'n' => 'DAI ERC', 'c' => $c_all, 'min' => 2500, 'max' => 200000],
                ['i' => 'daipoly.svg', 'm' => 'DAIPOLY', 'n' => 'DAI POLY', 'c' => $c_all, 'min' => 2500, 'max' => 200000],
                ['i' => 'btcbbsc.svg', 'm' => 'BTCBBSC', 'n' => 'BTCB BSC', 'c' => $c_all, 'min' => 2500, 'max' => 200000],
                ['i' => 'usdcerc.svg', 'm' => 'USDCERC', 'n' => 'USDC ERC', 'c' => $c_all, 'min' => 2500, 'max' => 200000],
                ['i' => 'avax.svg', 'm' => 'AVAX', 'n' => 'AVAX', 'c' => $c_all, 'min' => 2500, 'max' => 200000],
                ['i' => 'bnb.svg', 'm' => 'BNB', 'n' => 'BNB', 'c' => $c_all, 'min' => 2500, 'max' => 200000],
                ['i' => 'not.svg', 'm' => 'NOT', 'n' => 'NOT', 'c' => $c_all, 'min' => 2500, 'max' => 200000],
            ]

        ];

        foreach ($items as $type => $types) {
            foreach ($types as $i => $item) {
                ['i' => $image, 'm' => $method_alias, 'n' => $name, 'c' => $currencies, 'min' => $min, 'max' => $max] = $item;

                $group = $item['g'] ?? PaymentSystemGroup::Crypto->value;

                $update_field = [
                    'system_name' => $system_name,
                    'image_url' => '/img/paysystem/expay/' . $image,
                    'min_sum' => $min,
                    'max_sum' => $max,
                    'test_mode' => true,
                    'system_group' => $group,
                ];

                if (!empty($name)) $update_field['name'] = $name;

                foreach ($currencies as $c) {
                    PaymentSystems::updateOrCreate(
                        [
                            'type' => $type,
                            'currency_id' => $c,
                            'system_id' => Payin::TYPE_EXPAY,
                            'method_alias' => $system_name . '.' . $method_alias . '_'. Wallet::currencyName($c),
                        ],
                        $update_field
                    );
                }
            }
        }

    }
}
