<?php


namespace App\PaymentsAPI\Brpay;


use App\Helpers\Currency;
use App\Helpers\Hosts;
use App\Helpers\Popup;
use App\Services\IpInfo\IpInfo;
use App\Models\PaySystemsConfig;
use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * @docs https://merchant.br-pay.io/brapi/#api_dep
 */
class Brpay
{

    protected int $point_id = 271352;
    protected string $key = "ac5ef645f5b009abc491a88d789f0cd1";
    protected string $result_key = "60ff78c884d4746bc26dec26213c803b";
    protected string  $url = "https://api.brpay.io/pspay/";

    protected int $out_point_id = 271352;
    protected string $out_key = "ac5ef645f5b009abc491a88d789f0cd1";
    protected string  $out_result_key = "60ff78c884d4746bc26dec26213c803b";

//    protected $out_point_id = 271432;
//    protected $out_key = "28bfcff623c06e811efd0cfada4c4641";
//    protected $out_result_key = "7ac011821b5041fb9367ff00c6d2fdd5";

    public $is_error = false;
    public $error_code = 0;
    public $error_descr = "";

    public function __construct(int $currency_id = UserWallet::CURRENCY_RUB)
    {
        $conf = PaySystemsConfig::getSystemConfig(Payin::TYPE_BRPAY);
        $this->url = "https://api.br-pay.io/pspay/";

        if ($currency_id == UserWallet::CURRENCY_UAH) {
            $this->point_id = $conf['uah_point_id'] ?? 271683;
            $this->key = $conf['uah_key'] ?? "1ee3a72e23614822e36d2a3ca1d4be52";
            $this->result_key = $conf['uah_result_key'] ?? "2717793ead9fd9234505dff2d2579b71";
            $this->out_point_id = $conf['uah_out_point_id'] ?? 271683;
            $this->out_key = $conf['uah_out_key'] ?? "1ee3a72e23614822e36d2a3ca1d4be52";
            $this->out_result_key = $conf['uah_out_result_key'] ?? "2717793ead9fd9234505dff2d2579b71";
        } elseif ($currency_id == UserWallet::CURRENCY_INR) {
                $this->point_id = $conf['inr_point_id'] ?? 271810;
                $this->key = $conf['inr_key'] ?? "24015e90fdcb849d85e2062432b35575";
                $this->result_key = $conf['inr_result_key'] ?? "e4e02d91b21f47e7498cbeb9c80ea056";
//                $this->out_point_id = $conf['uah_out_point_id'] ?? 271683;
//                $this->out_key = $conf['uah_out_key'] ?? "1ee3a72e23614822e36d2a3ca1d4be52";
//                $this->out_result_key = $conf['uah_out_result_key'] ?? "2717793ead9fd9234505dff2d2579b71";
        } elseif ($currency_id == UserWallet::CURRENCY_RUB) {
            $this->point_id = 271352;
            $this->key = "a6b7d618bf7aee07dd6dc4846c0d4ae4";
            $this->result_key = "38b6012d2feae9da3e64b4ee44dde7ee";
            $this->out_point_id = 271352;
            $this->out_key = 'a6b7d618bf7aee07dd6dc4846c0d4ae4';
            $this->out_result_key = '38b6012d2feae9da3e64b4ee44dde7ee';
        } else {
            $this->point_id = $conf['point_id'] ?? 271352;
            $this->key = $conf['key'] ?? "ac5ef645f5b009abc491a88d789f0cd1";
            $this->result_key = $conf['result_key'] ?? "60ff78c884d4746bc26dec26213c803b";
            $this->out_point_id = $conf['out_point_id'] ?? 271352;
            $this->out_key = $conf['out_key'] ?? 'ac5ef645f5b009abc491a88d789f0cd1';
            $this->out_result_key = $conf['out_result_key'] ?? '60ff78c884d4746bc26dec26213c803b';
        }
    }

    public function setKeysForMethod(string $pay_method): void
    {
        if ($pay_method === 'Brpay.CRYPTOPAY_RUB_P2P_SBP') {
            $system = "CRYPTOPAY_RUB_P2P_SBP";
            $this->point_id = 272441;
            $this->key = "68d5f383a5d02a5e4e5348c9ec2f7d32";
            $this->result_key ="7f9e1ceb1ee9aad1de91f28f9c963c43";
            $this->out_point_id = 272441;
            $this->out_key = '68d5f383a5d02a5e4e5348c9ec2f7d32';
            $this->out_result_key = '7f9e1ceb1ee9aad1de91f28f9c963c43';
            $lang = 'ru';
        }

        if ($pay_method === 'Brpay.PHONE_SBP') {
            $system = "CRYPTOPAY_RUB_P2P_SBP";
            $this->point_id = 272441;
            $this->key = "68d5f383a5d02a5e4e5348c9ec2f7d32";
            $this->result_key ="7f9e1ceb1ee9aad1de91f28f9c963c43";
            $this->out_point_id = 272441;
            $this->out_key = '68d5f383a5d02a5e4e5348c9ec2f7d32';
            $this->out_result_key = '7f9e1ceb1ee9aad1de91f28f9c963c43';
            $lang = 'ru';
        }


        if ($pay_method === 'Brpay.CRYPTOPAY_RUB_P2P_NEW') {
            $system = "CRYPTOPAY_RUB_P2P_NEW";
            $this->point_id = 272441;
            $this->key = "68d5f383a5d02a5e4e5348c9ec2f7d32";
            $this->result_key ="7f9e1ceb1ee9aad1de91f28f9c963c43";
            $lang = 'ru';
        }

        if ($pay_method == 'Brpay.CARD_RUB_P2P') {
            $currency_name = 'RUB';
            $system = 'CRYPTOPAY_RUB_P2P_NEW';
            $this->point_id = 272441;
            $this->key = "68d5f383a5d02a5e4e5348c9ec2f7d32";
            $this->result_key ="7f9e1ceb1ee9aad1de91f28f9c963c43";
            $this->out_point_id = 272441;
            $this->out_key = '68d5f383a5d02a5e4e5348c9ec2f7d32';
            $this->out_result_key = '7f9e1ceb1ee9aad1de91f28f9c963c43';
            $lang = 'ru';
        }

        if ($pay_method == 'Brpay.CRYPTOPAY_RUB_P2P_GNP_AB_SBP') {
            $currency_name = 'RUB';
            $system = 'CRYPTOPAY_RUB_P2P_GNP_AB_SBP';
            $lang = 'ru';
            $this->point_id = 272441;
            $this->key = "68d5f383a5d02a5e4e5348c9ec2f7d32";
            $this->result_key ="7f9e1ceb1ee9aad1de91f28f9c963c43";
        }

        if ($pay_method === 'Brpay.CRYPTOPAY_RUB_GNP_VKG_SBP') {
            $currency_name = 'RUB';
            $system = 'CRYPTOPAY_RUB_GNP_VKG_SBP';
            $lang = 'ru';
            $this->point_id = 272441;
            $this->key = "68d5f383a5d02a5e4e5348c9ec2f7d32";
            $this->result_key ="7f9e1ceb1ee9aad1de91f28f9c963c43";
        }

        if ($pay_method == 'Brpay.CRYPTOPAY_RUB_ACC') {
            $currency_name = 'RUB';
            $system = 'CRYPTOPAY_RUB_ACC';
            $this->point_id = 272441;
            $this->key = "68d5f383a5d02a5e4e5348c9ec2f7d32";
            $this->result_key ="7f9e1ceb1ee9aad1de91f28f9c963c43";
            $lang = 'ru';
        }

        if ($pay_method == 'Brpay.visa_trusted') {
            $system = 'CARD_TRUSTED';
            $this->point_id = 272496;
            $this->key = "12c47614002f3fb789f7d09f31802aef";
            $this->result_key ="825c54db410a14bc852e09db116f9d7c";
        }

        if ($pay_method === 'Brpay.CRYPTOPAY_RUB_GNP_AKG_SBP') {
            $currency_name = 'RUB';
            $system = 'CRYPTOPAY_RUB_GNP_AKG_SBP';
            $lang = 'ru';
            $this->point_id = 272441;
            $this->key = "68d5f383a5d02a5e4e5348c9ec2f7d32";
            $this->result_key ="7f9e1ceb1ee9aad1de91f28f9c963c43";
        }

        if ($pay_method === 'Brpay.CRYPTOPAY_RUB_GNP_SBER_SBP') {
            $currency_name = 'RUB';
            $system = 'CRYPTOPAY_RUB_GNP_SBER_SBP';
            $lang = 'ru';
            $this->point_id = 272441;
            $this->key = "68d5f383a5d02a5e4e5348c9ec2f7d32";
            $this->result_key ="7f9e1ceb1ee9aad1de91f28f9c963c43";
        }

        if ($pay_method === 'BrPay.CARD_KZT_FTD') {
            $currency_name = 'KZT';
            $system = 'CARD_KZT_FTD';
            $lang = 'ru';
            $this->point_id = 273083;
            $this->key = "fbe5508a2934f2260b37b8ba7dc920e3";
            $this->result_key ="1c2b705853b4f7b8d208d69ce10c733e";
            $this->out_point_id = 273083;
            $this->out_key = "fbe5508a2934f2260b37b8ba7dc920e3";
            $this->out_result_key ="1c2b705853b4f7b8d208d69ce10c733e";
        }

        if ($pay_method === 'BrPay.CARD_KZT_STD') {
            $currency_name = 'KZT';
            $system = 'CARD_KZT_STD';
            $lang = 'ru';
            $this->point_id = 273084;
            $this->key = "24b28515cc26a3c3545391d00079458a";
            $this->result_key ="b7b61fd9dadedfa0b1213498a98d8be0";
            $this->out_point_id = 273084;
            $this->out_key = "24b28515cc26a3c3545391d00079458a";
            $this->out_result_key ="b7b61fd9dadedfa0b1213498a98d8be0";
        }
    }

    public function initPayment(string $id, MUser $user, $amount, string $currency_name, $pay_method = '', $requisites = [])
    {
        $system = 'CARD';

        $method = 'init_payment';
        $name = $user->login;
        if (empty($user->login) || strlen($user->login) < 5) $name = 'kraken_player';
        $phone = empty($user->phone) ? '8888888888' : $user->phone;
        $mail = empty($user->email) ? 'nomail@test.com' : $user->email;
        $lang = 'en';

        if ($currency_name === 'RUB') {
            $system = 'CARD';
            $lang = 'ru';
        }

        if ($pay_method === 'Brpay.CARD_RUB_PW') {
            $system = "CARD_RUB_PW";
        }

//        if ($currency_name === 'KZT') {
//            $system = "CARD_EUR_M";
//            $lang = 'ru';
//            $amount = round(Currency::convert($amount, 'KZT', 'EUR'), 2);
//        }

        if ($currency_name === 'UAH') {
            $system = "CARDS_UAH";
            $lang = 'ru';
        }

        if ($currency_name === 'INR') {
            $system = "MAIN_INR_QP";
            if ($pay_method === 'CARD_INR_QP') $system = $pay_method;

            if ($pay_method !== 'Brpay.cardinr' && $pay_method !== 'CARD_INR_QP') {
                $system = $pay_method;
                if (!in_array($pay_method, ['CARD_INR_QP', 'UPI_INR_QP', 'NETBANK_INR_QP'])) $system = 'WALLET_INR_QP';
                $params = [
                    'sp_outlet_id' => $this->point_id,
                    'sp_order_id' => $id,
                    'sp_amount' => number_format($amount, 2, '.', ''),
                    'sp_currency' => $currency_name,
                    'sp_description' => 'krakencassino',
                    'sp_user_ip' => request()->ip(),
                    'sp_user_name' => $name,
                    'sp_user_phone' => $phone,
                    'sp_user_contact_email' => $mail,
                    'sp_success_url' => action('PayController@success'),
                    'sp_failure_url' => action('PayController@fail'),
                    'sp_result_url' => Hosts::getFinHost($currency_name).'/api/brpay/pay',
                    'sp_lifetime' => 600,
                    'sp_payment_system' => $system,
                    'sp_language' => $lang,
                    'sp_host2host' => 1,
                    'sp_real_url' => route('landing', [], true),
//                    'direct_acs' => 1,
                ];
                $params = array_merge($params, $requisites);
                $result = $this->sendQuery($method, $params);
                if ($result['sp_status'] !== 'ok') {
                    Log::channel('pay')->error('brpay error');
                    return ['error' => 'system error'];
                }
                $url = $result['sp_host2host']['url'];
                $params = array_merge([
                    'sp_payment_system' => $system,
                    'direct_acs' => 1,
                ], $requisites);
                unset($params['_token']);
                unset($params['payment-method']);
                $url .= '&'. http_build_query($params);
//                $result = $this->sendQuery('', ['direct_acs' => 1,], false, $url);
                $result = $this->query($url, $params);
                return $result;
            }
        }

        if ($pay_method === 'Brpay.CRYPTOPAY_RUB_P2P_SBP') {
            $system = "CRYPTOPAY_RUB_P2P_SBP";
            $this->point_id = 272441;
            $this->key = "68d5f383a5d02a5e4e5348c9ec2f7d32";
            $this->result_key ="7f9e1ceb1ee9aad1de91f28f9c963c43";
            $this->out_point_id = 272441;
            $this->out_key = '68d5f383a5d02a5e4e5348c9ec2f7d32';
            $this->out_result_key = '7f9e1ceb1ee9aad1de91f28f9c963c43';
            $lang = 'ru';
        }


        if ($pay_method === 'Brpay.CRYPTOPAY_RUB_P2P_NEW') {
            $system = "CRYPTOPAY_RUB_P2P_NEW";
            $this->point_id = 272441;
            $this->key = "68d5f383a5d02a5e4e5348c9ec2f7d32";
            $this->result_key ="7f9e1ceb1ee9aad1de91f28f9c963c43";
            $lang = 'ru';
        }

        if ($pay_method == 'Brpay.CARD_RUB_P2P') {
            $currency_name = 'RUB';
            $system = 'CRYPTOPAY_RUB_P2P_NEW';
            $this->point_id = 272441;
            $this->key = "68d5f383a5d02a5e4e5348c9ec2f7d32";
            $this->result_key ="7f9e1ceb1ee9aad1de91f28f9c963c43";
            $this->out_point_id = 272441;
            $this->out_key = '68d5f383a5d02a5e4e5348c9ec2f7d32';
            $this->out_result_key = '7f9e1ceb1ee9aad1de91f28f9c963c43';
            $lang = 'ru';
        }

        $sp_payment_bank = "";
        if ($pay_method === 'Brpay.CRYPTOPAY_RUB_P2P_GNP_AB_SBP') {
            $currency_name = 'RUB';
            $system = 'CRYPTOPAY_RUB_P2P_GNP_AB_SBP';
            $lang = 'ru';
            $this->point_id = 272441;
            $this->key = "68d5f383a5d02a5e4e5348c9ec2f7d32";
            $this->result_key ="7f9e1ceb1ee9aad1de91f28f9c963c43";
            $sp_payment_bank = 'sberbank';
        }
        if ($pay_method === 'Brpay.CRYPTOPAY_RUB_GNP_VKG_SBP') {
            $currency_name = 'RUB';
            $system = 'CRYPTOPAY_RUB_GNP_VKG_SBP';
            $lang = 'ru';
            $this->point_id = 272441;
            $this->key = "68d5f383a5d02a5e4e5348c9ec2f7d32";
            $this->result_key ="7f9e1ceb1ee9aad1de91f28f9c963c43";
        }
        if ($pay_method === 'Brpay.CRYPTOPAY_RUB_P2P_GNP_ABFTD_SBP') {
            $currency_name = 'RUB';
            $system = 'CRYPTOPAY_RUB_P2P_GNP_ABFTD_SBP';
            $lang = 'ru';
            $this->point_id = 272441;
            $this->key = "68d5f383a5d02a5e4e5348c9ec2f7d32";
            $this->result_key ="7f9e1ceb1ee9aad1de91f28f9c963c43";
            $sp_payment_bank = 'sberbank';
        }
        if ($pay_method === 'Brpay.CRYPTOPAY_RUB_P2P_GNP_AB_SBP-T') {
            $currency_name = 'RUB';
            $system = 'CRYPTOPAY_RUB_P2P_GNP_AB_SBP';
            $lang = 'ru';
            $this->point_id = 272441;
            $this->key = "68d5f383a5d02a5e4e5348c9ec2f7d32";
            $this->result_key ="7f9e1ceb1ee9aad1de91f28f9c963c43";
            $sp_payment_bank = 'tinkoff';
        }
        if ($pay_method === 'Brpay.CRYPTOPAY_RUB_P2P_GNP_ABFTD_SBP-T') {
            $currency_name = 'RUB';
            $system = 'CRYPTOPAY_RUB_P2P_GNP_ABFTD_SBP';
            $lang = 'ru';
            $this->point_id = 272441;
            $this->key = "68d5f383a5d02a5e4e5348c9ec2f7d32";
            $this->result_key ="7f9e1ceb1ee9aad1de91f28f9c963c43";
            $sp_payment_bank = 'tinkoff';
        }

        if ($pay_method == 'Brpay.CRYPTOPAY_RUB_ACC') {
            $currency_name = 'RUB';
            $system = 'CRYPTOPAY_RUB_ACC';
            $this->point_id = 272441;
            $this->key = "68d5f383a5d02a5e4e5348c9ec2f7d32";
            $this->result_key ="7f9e1ceb1ee9aad1de91f28f9c963c43";
            $lang = 'ru';
        }

        if ($pay_method == 'Brpay.visa_trusted') {
            $this->point_id = 272496;
            $this->key = "12c47614002f3fb789f7d09f31802aef";
            $this->result_key ="825c54db410a14bc852e09db116f9d7c";
            $name = $user->personalData()->name.' '.$user->personalData()->surname;
            if (empty($name)) $name = $user->login;
            $system = 'CARD_TRUSTED';
        }

        if ($pay_method === 'Brpay.CRYPTOPAY_RUB_GNP_AKG_SBP') {
            $currency_name = 'RUB';
            $system = 'CRYPTOPAY_RUB_GNP_AKG_SBP';
            $lang = 'ru';
            $this->point_id = 272441;
            $this->key = "68d5f383a5d02a5e4e5348c9ec2f7d32";
            $this->result_key ="7f9e1ceb1ee9aad1de91f28f9c963c43";
        }

        if ($pay_method === 'Brpay.CRYPTOPAY_RUB_GNP_SBER_SBP') {
            $currency_name = 'RUB';
            $system = 'CRYPTOPAY_RUB_GNP_SBER_SBP';
            $lang = 'ru';
            $this->point_id = 272441;
            $this->key = "68d5f383a5d02a5e4e5348c9ec2f7d32";
            $this->result_key ="7f9e1ceb1ee9aad1de91f28f9c963c43";
        }

        if ($pay_method === 'BrPay.CARD_KZT_FTD') {
            $currency_name = 'KZT';
            $system = 'CARD_KZT_FTD';
            $lang = 'ru';
            $this->point_id = 273083;
            $this->key = "fbe5508a2934f2260b37b8ba7dc920e3";
            $this->result_key ="1c2b705853b4f7b8d208d69ce10c733e";
            $this->out_point_id = 273083;
            $this->out_key = "fbe5508a2934f2260b37b8ba7dc920e3";
            $this->out_result_key ="1c2b705853b4f7b8d208d69ce10c733e";
        }

        if ($pay_method === 'BrPay.CARD_KZT_STD') {
            $currency_name = 'KZT';
            $system = 'CARD_KZT_STD';
            $lang = 'ru';
            $this->point_id = 273084;
            $this->key = "24b28515cc26a3c3545391d00079458a";
            $this->result_key ="b7b61fd9dadedfa0b1213498a98d8be0";
            $this->out_point_id = 273084;
            $this->out_key = "24b28515cc26a3c3545391d00079458a";
            $this->out_result_key ="b7b61fd9dadedfa0b1213498a98d8be0";
        }

        $params = [
            'sp_outlet_id' => $this->point_id,
            'sp_order_id' => $id,
            'sp_amount' => number_format($amount, 2, '.', ''),
            'sp_currency' => $currency_name,
            'sp_description' => 'krakencassino',
            'sp_user_ip' => request()->ip(),
            'sp_user_name' => $name,
            'sp_user_phone' => $phone,
            'sp_user_contact_email' => $mail,
            'sp_customer_id' => $user->id,
            'sp_success_url' => action('PayController@success'),
            'sp_failure_url' => action('PayController@fail'),
            'sp_result_url' => Hosts::getFinHost($currency_name).'/api/brpay/pay',
            'sp_lifetime' => 600,
            'sp_payment_system' => $system,
            'sp_language' => $lang,
            'sp_real_url' => route('pay.close-tab', [], true),
        ];
        if (!empty($sp_payment_bank)) {
            $params['sp_payment_bank'] = $sp_payment_bank;
        }

        $result = $this->sendQuery($method, $params);

        return $result;
    }

    public function payList($amount, $currency_name)
    {
        $method = 'ps_list';
        $params = [
            'sp_outlet_id' => $this->point_id,
            'sp_amount' => number_format($amount, 2, '.', ''),
            'sp_currency' => $currency_name,
        ];

        return $this->sendQuery($method, $params);
    }

    public function depositStatus($id)
    {
        $method = 'get_status';
        $params = [
            'sp_outlet_id' => $this->point_id,
            'sp_order_id' => $id,
        ];

        return $this->sendQuery($method, $params);
    }

    public function viewRedirectForm(PayoutTransaction $transaction)
    {
        $params = [
            'sp_outlet_id' => $this->point_id,
            'sp_order_id' => $transaction->id,
            'sp_user_name' => 'testuser',
            'sp_user_phone' => '8888888888',
            'sp_user_contact_email' => 'nomail@test.com',
            'sp_amount' => number_format($transaction->sum, 2, '.', ''),
            'sp_currency' => $transaction->currency,
            'sp_description' => 'krakencassino пополнение',
            'sp_user_ip' => request()->ip(),
            'sp_lifetime' => 1200,
            'sp_payment_system' => 'CARD',
        ];
        $this->sign('payment', $params);
        $params['url'] = $this->url . 'payment';
        return view('pay.brpay-form', $params)->render();
    }

    /**
     * payout
     * @param float $amount
     * @param string $account
     * @param string $transaction_id
     * @return bool|mixed
     */
    public function transfer(float $amount, string $account, string $transaction_id)
    {
        $method = "transfer_to_card_rus";
        $params = [
            'sp_outlet_id' => $this->out_point_id,
            'sp_destination_card' => $account,
            'sp_amount' => number_format($amount, 2, '.', ''),
            'sp_order_id' => $transaction_id,
        ];

        return $this->sendQuery($method, $params, true);
    }

    public function transferCard(float $amount, string $account, string $transaction_id, string $currency_name)
    {
        $method = "transfer_to_card";
        $params = [
            'sp_outlet_id' => $this->out_point_id,
            'sp_destination_card' => $account,
            'sp_amount' => number_format($amount, 2, '.', ''),
            'sp_order_id' => $transaction_id,
            'sp_currency' => $currency_name,
        ];

        return $this->sendQuery($method, $params, true);
    }

    public function transferPhone(float $amount, string $phone, string $bank, string $transaction_id)
    {
        $method = "transfer_to_card_rus";
        $params = [
            'sp_outlet_id' => $this->out_point_id,
            'sp_destination_phone' => $phone,
            'sp_destination_bank' => $bank,
            'sp_amount' => number_format($amount, 2, '.', ''),
            'sp_order_id' => $transaction_id,
        ];

        return $this->sendQuery($method, $params, true);
    }

    /**
     * payout
     * @param float $amount
     * @param string $account
     * @param string $transaction_id
     * @return bool|mixed
     */
    public function transferVisa(float $amount, string $account, string $transaction_id)
    {
        $method = "transfer_to_card";
        $params = [
            'sp_outlet_id' => $this->out_point_id,
            'sp_destination_card' => $account,
            'sp_amount' => number_format($amount, 2, '.', ''),
            'sp_order_id' => $transaction_id,
        ];

        return $this->sendQuery($method, $params, true);
    }

    /**
     * check payout status
     * @param int $order_id unique id on BR side
     * @return bool|mixed
     */
    public function payoutStatus(int $order_id)
    {
        $method = "get_payout_status";
        $params = [
            'sp_outlet_id' => $this->out_point_id,
            'sp_payout_id' => $order_id,
        ];

        return $this->sendQuery($method, $params, true);
    }

    private function sign(string $method, array &$params, $payout = false)
    {
        $params['sp_salt'] = Str::random(16);
        $values = $this->get_array_values($params);
        if ($payout) {
            $hash_string = $method . ';' . $values . $this->out_key;
        } else {
            $hash_string = $method . ';' . $values . $this->key;
        }
        $hash = hash("md5", $hash_string);
        $params['sp_sig'] = $hash;
        return $hash;
    }

    public function checkSign($data, $sign)
    {
        if (isset($data['sp_sig'])) unset($data['sp_sig']);
        $filtered = array_filter($data, function ($k, $value) {
            return preg_match('#^sp_.*$#', $value);
        }, ARRAY_FILTER_USE_BOTH);
        $values = $this->get_array_values($filtered);
        $hash_string = "pay;" . $values . $this->result_key;
        $hash = hash("md5", $hash_string);
        $params['sp_sig'] = $hash;
        return $sign === $hash;
    }

    public function checkSignTrusted($data, $sign)
    {
        $this->point_id = 272496;
        $this->key = "12c47614002f3fb789f7d09f31802aef";
        $this->result_key ="825c54db410a14bc852e09db116f9d7c";

        if (isset($data['sp_sig'])) unset($data['sp_sig']);
        $filtered = array_filter($data, function ($k, $value) {
            return preg_match('#^sp_.*$#', $value);
        }, ARRAY_FILTER_USE_BOTH);
        $values = $this->get_array_values($filtered);
        $hash_string = "pay;" . $values . $this->result_key;
        $hash = hash("md5", $hash_string);
        $params['sp_sig'] = $hash;
        return $sign === $hash;
    }

    public function test_sign()
    {
        $params = [
            'sp_a_param' => 'value1',
            'sp_z_param' => [
                'sp_q_subparam' => 'subvalue2',
                'sp_m_subparam' => 'subvalue1',
            ],
            'sp_b_param' => 'value2',
        ];
        return $this->sign('payment', $params);
    }

    public function get_array_values(array $data): string
    {
        ksort($data, SORT_STRING);
        $str = '';
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $str .= $this->get_array_values($v);
            } else {
                $str .= $v . ";";
            }
        }
        return $str;
    }

    /**
     * Converts array to http headers
     * @param array $ar_headers array with headers
     * @return array headers format "header-name: value"
     */
    private function arrayToHeaders(array $ar_headers)
    {
        $curl_headers = [];
        foreach ($ar_headers as $k => $v) {
            $curl_headers[] = '' . $k . ': ' . $v;
        }
        return $curl_headers;
    }

    private function sendQuery(string $method, ?array $data = [], $payout = false, string $direct_url = '')
    {
        $this->is_error = false;
        $this->sign($method, $data, $payout);

        $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $json_params = [
            'sp_json' => $json,
        ];

        $url = $this->url . $method;
        if (!empty($direct_url)) $url = $direct_url;

        Log::channel('pay')->info('BrPay url ' . $url);
        Log::channel('pay')->info('BrPay params' . var_export($json_params, true));

        $headers = [
            'Content-Type' => 'application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->arrayToHeaders($headers));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_params);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        $response = curl_exec($ch);

        curl_close($ch);

        Log::channel('pay')->info('BrPay response:' . $response);

        if (!$response) {
            Log::channel('pay')->error('Can`t connect to BrPay payment system');
            $this->is_error = true;
            $this->error_descr = "Can`t connect to BrPay payment system";
            return false;
        }

        $resp = json_decode($response, true);

        if ($resp['sp_status'] !== 'ok') {
            Log::channel('pay')->error('BrPay error');
            Log::channel('pay')->error(var_export($resp, true));
            $this->is_error = true;
            $this->error_descr = $resp['sp_error_description'] ?? 'BR pay error!';
            $this->error_code = $resp['sp_error_code'] ?? -1;
            return false;
        }

        return $resp;
    }

    function query($url, $data) {
        Log::channel('pay')->info('BrPay url ' . $url);
        Log::channel('pay')->info('BrPay params' . var_export(http_build_query($data), true));
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        $response = curl_exec($curl);
        Log::channel('pay')->info('BrPay response:' . $response);
        $resp = json_decode($response, true);
        curl_close($curl);
        return $resp;
    }

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . " callback:", $request->all());
        Log::channel('pay')->info(__CLASS__ . " callback raw:". var_export($request->getContent(), true));
        $data = $request->all();
        Log::channel('pay')->info(__CLASS__ . " callback data:", $data);

        $targetUrl = "http://127.0.0.1:23106/pay/brpay";
        $response = Http::withHeaders($request->headers->all())
            ->withBody($request->getContent(), $request->header('Content-Type'))
            ->send($request->method(), $targetUrl);
        Log::channel('pay')->info('brpay callback response ' . var_export($response->body(), true));
        return response($response->body(), $response->status())->withHeaders($response->headers());

//        $trans_id = $request->get('sp_order_id');
//        if (!$trans_id) {
//            Log::channel('pay')->error('orderId field not set');
//            return response('Платеж не найден', 500);
//        }
//
//        $data = $request->all();
//
//        $currency = UserWallet::getCurrencyCode($data['sp_currency']);
//        $api = new Brpay($currency);
//        $sign = $data['sp_sig'];
//
//        if (!$api->checkSign($data, $sign) && !$api->checkSignTrusted($data, $sign)) {
//            Log::channel('pay')->error('Wrong sign, possible hack');
////            return response('', 500);
//        }
//
//        DB::beginTransaction();
//
//        // Find transaction.
//        /** @var PayoutTransaction $transaction */
//        $transaction = PayoutTransaction::where('id', $trans_id)
//            ->whereIn('status_id', [
//                PayoutTransaction::STATUS_BRPAY_PENDING,
//                PayoutTransaction::STATUS_BRPAY_FAIL,
//            ])
//            ->lockForUpdate()
//            ->first();
//
//        if ($transaction === null) {
//            Log::channel('pay')->error('Transaction with id ' . $trans_id . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
//            DB::commit();
//            return response('OK', 500);
//        }
//
//        if (intval($data['sp_result']) !== 1) {
//            $transaction->status_id = PayoutTransaction::STATUS_BRPAY_FAIL;
//            $transaction->save();
//            DB::commit();
//            return response('OK', 200);
//        }
//
//        // Find user which create this transaction.
//        /** @var MUser $user $user */
//        $user = MUser::where('id', $transaction->user_id)->first();
//
//        if ($user === null) {
//            Log::channel('pay')->error('User of the transaction is not found.');
//            DB::commit();
//            return response('User not found.', 500);
//        }
//
//        $amount = $data['sp_amount'];
//
//
//        $fee = $amount - ($data['sp_net_amount'] ?? $amount);
//        if ($fee === 0) $fee = $amount * 0.1;
//
//        if ($currency === UserWallet::CURRENCY_EUR) {
//            Log::channel('pay')->info('converting currency from EUR to '.$transaction->currency);
//            $currency = UserWallet::getCurrencyCode($transaction->currency);
//            $amount = round(Currency::convert($amount, 'EUR', $transaction->currency), 2);
//            $fee = round(Currency::convert($fee, 'EUR', $transaction->currency), 2);
//            $diff_prc = $amount / $transaction->sum;
//            if ($diff_prc < 1 && $diff_prc >= 0.97) $amount = $transaction->sum;
//        }
//
//        $transaction->sum = $amount;
//        $transaction->fee = round($fee, 2);
//        $transaction->total_sum = $amount + $fee;
//        $transaction->card_number = $data['sp_card_pan']??'';
//
//        try {
//            Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_BRPAY_SUCCESS, $currency);
//        } catch (\Exception $e) {
//            DB::rollBack();
//            Log::channel('pay')->error($e->getMessage());
//        }
//
//        DB::commit();
//
//        return response('OK', 200);
    }
}
/*
 * success
 * [2021-02-14 18:36:33] local.INFO: Start processing brpay
array (
  'sp_result' => '1',
  'sp_order_id' => '1880839',
  'sp_payment_id' => '40921677',
  'sp_payment_system' => 'TEST',
  'sp_amount' => '100.00',
  'sp_ps_amount' => '100.00',
  'sp_net_amount' => '100.00',
  'sp_currency' => 'EUR',
  'sp_salt' => '25463',
  'sp_sig' => 'b171d46eb7043a8bbce22f40cd9d9a62',
)

fail
[2021-02-14 18:45:39] local.INFO: Start processing brpay
array (
  'sp_result' => '0',
  'sp_order_id' => '1880840',
  'sp_payment_id' => '40921678',
  'sp_payment_system' => 'TEST',
  'sp_amount' => '200.00',
  'sp_currency' => 'EUR',
  'sp_salt' => '29615',
  'sp_sig' => '209e5ced1c5e9228cc867203a43ddfd9',
)

 */
