<?php


namespace App\PaymentsAPI;


use App\Models\PaySystemsConfig;
use App\Payin\Payin;
use App\UserWallet;
use Illuminate\Support\Facades\Log;

/**
 * Class ObmenkaUa
 * @package App\PaymentsAPI
 * @see https://acquiring.obmenka.ua/docapi_
 */
class ObmenkaUa
{
    const STATUS_CREATED = 'CREATED';
    const STATUS_PAYIN = 'PAYIN';
    const STATUS_PAYED = 'PAYED';//final
    const STATUS_FAILED = 'FAILED';//final
    const STATUS_CANCELED = 'CANCELED';//final
    const STATUS_PAYED_RECALC = 'PAYED_RECALC';//final
    const STATUS_FINISHED = 'FINISHED';//test only
    const STATUS_PAYIN_ERROR = 'PAYIN_ERROR';//test only

    const ERROR_CONNECTION = -1;
    const ERROR_BALANCE = -2;

    protected int $id = 2854;
    protected string $key = '5f75df1144287464621668';
    protected string $test_key = '5f75df1144287464621661';//test only

    protected $url = 'https://acquiring_api.obmenka.ua';

    public $isError = false;
    public $errCode = 0;
    public $errDescr = '';

    public function __construct()
    {
        $conf = PaySystemsConfig::getSystemConfig(Payin::TYPE_OBMENKA);
        $this->id = $conf['id'] ?? 2854;
        $this->key = $conf['key'] ?? '5f75df1144287464621668';
    }

    /**
     * get all currencies balances
     * @return mixed
     */
    public function getBalances()
    {
        $method = '/api/payment/balance';
        $responce = $this->send_query($method);
        if(!is_array($responce) || !isset($responce['balances']))
        {
            Log::error(__CLASS__.' get balance error');
            Log::error(__CLASS__.' response:'.var_export($responce, true));
            $this->isError = true;
            $this->errCode = self::ERROR_BALANCE;
            $this->errDescr = 'Get balance error';
        }
        return $responce['balances'];
    }

    /**
     * get balance for currency
     * @param string $o_currency AdvCash, USD - advcash.usd
                            Bitcoin, BTC - bitcoin
                            Bitcoin Cash, BCH - bitcoin_cash
                            Litecoin, LTC - litecoin
                            QIWI, RUR - qiwi
                            Tether, USDT - tether
                            Visa/Master, RUR - visamaster.rur
                            Visa/Master, UAH - visamaster.uah
                            Webmoney, WME - wme
                            Webmoney, WMZ - wmz
                            Webmoney, WMR - wmr
                            Яндекс.Деньги, RUR - yandex
     * @return float currency balance, 0 if error
     */
    public function getCurrBalance(string $o_currency):float
    {
        $balances = $this->getBalances();
        foreach ($balances as $balance){
            if($balance['currency'] == $o_currency){
                return $balance['available'];
            }
        }

        Log::error(__CLASS__.' get balance error currency not fount');
        Log::error(__CLASS__.' currency:'.$o_currency);
        $this->isError = true;
        $this->errCode = self::ERROR_BALANCE;
        $this->errDescr = 'currency not found '.$o_currency;

        return 0;
    }

    public function createBill(int $paymentId, string $o_currency, float $amount, string $description = 'Krakencassino deposit')
    {
        $method = '/api/einvoice/create';
        $data = [
            'payment_id' => $paymentId,
            'currency' => $o_currency,
            'amount' => $amount,
            'description' => $description,
            'success_url' => route('pay.success'),
            'fail_url' => route('pay.fail'),
            'status_url' => 'https://krcas.com/pay/obmenka/deposit',
        ];

        $response = $this->send_query($method, $data);
        if(!$this->isError){
            return $response['tracking'];
        }
        $this->isError = true;
        return '';
    }

    public function processBill(int $paymentId, ?string $tacking = null)
    {
        $method = '/api/einvoice/process';
        $data = [
            'payment_id' => $paymentId,
            '$tacking' => $tacking,
        ];

        $response = $this->send_query($method, $data);
        if(!$this->isError){
            return $response['pay_link'];
        }

        return '';
    }

    public function billStatus(int $paymentId, ?string $tacking = null)
    {
        $method = '/api/einvoice/status';
        $data = [
            'payment_id' => $paymentId,
            '$tacking' => $tacking,
        ];

        $response = $this->send_query($method, $data);
        return $response;
    }

    public function createPayout(int $payment_id, string $recipient, string $o_currency, float $amount, string $phone = '', string $description = 'Krakencassino payout' )
    {
        $method = '/api/payment/create';
        $data = [
            'recipient' => $recipient,
            'amount' => $amount,
            'currency' => $o_currency,
            'phone' => $phone,
            'description' => $description,
            'payment_id' => $payment_id,
            'status_url' => 'https://krakencassino.com/pay/obmenka/refresh',
        ];

        $response = $this->send_query($method, $data);
        return $response;
    }

    public function validatePayout(int $paymentId, ?string $tacking = null):bool
    {
        $method = '/api/payment/validate';
        $data = [
            'payment_id' => $paymentId,
            '$tacking' => $tacking,
        ];

        $response = $this->send_query($method, $data);

        if(!$this->isError){
            if(!$response['is_valid']){
                $this->isError = true;
                $this->errDescr = $response['reason'];
            }
            return $response['is_valid'];
        }
        return false;
    }

    public function processPayout(int $paymentId, ?string $tacking = null)
    {
        $method = '/api/payment/process';
        $data = [
            'payment_id' => $paymentId,
            '$tacking' => $tacking,
        ];

        $response = $this->send_query($method, $data);
        return $response;
    }

    public function payoutStatus(int $paymentId, ?string $tacking = null)
    {
        $method = '/api/payment/status';
        $data = [
            'payment_id' => $paymentId,
            '$tacking' => $tacking,
        ];

        $response = $this->send_query($method, $data);
        return $response;
    }

    public function send_query(string $method, array $data = [])
    {
        $sign = $this->sign(json_encode($data));
        $url = rtrim($this->url, '/' ) . '/' . ltrim( $method, '/' );
        Log::info(__CLASS__.' query url:'.$url.' data'.json_encode($data));

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "DPAY_CLIENT:$this->id",
            "DPAY_SECURE:$sign",
            'Content-Type:application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $resp = curl_exec($ch);
        $responce = json_decode($resp, true);

        Log::info(__CLASS__.' response:'.var_export($responce, true));

        $this->isError = false;

        if(empty($responce)){
            Log::error(__CLASS__.' Network error');
            Log::error(__CLASS__.' response:'.var_export($responce, true));
            $this->isError = true;
            $this->errCode = self::ERROR_CONNECTION;
            $this->errDescr = 'Network error';
        }

        if(isset($responce['error'])){
            Log::error(__CLASS__.' request error');
            Log::error(__CLASS__.' response:'.var_export($responce, true));
            $this->isError = true;
            $this->errCode = $responce['error']['code'];
            $this->errDescr = $responce['error']['message'];
        }

        return $responce;
    }

    /**
     * get sign
     * @param string $data in json
     * @return string
     */
    private function sign(string $data)
    {
        return base64_encode(md5($this->key . base64_encode(sha1($data, true)) . $this->key, true));
    }

    public static function getObmenkaCurrency($method){
        return mb_strtoupper(str_replace('Obmenka.', '', $method));
    }

    public static function getCurrencyCode($o_currency)
    {
        if($o_currency == 'RUR') return UserWallet::CURRENCY_RUB;
        if($o_currency == 'UAH') return UserWallet::CURRENCY_UAH;

        throw new \Exception('Unknown Obmenka currency '.$o_currency);
    }
}
/**
* INSERT INTO `payout_transactions_statusTransaction` (`id`, `value`) VALUES ('131', 'OBMENKA_PENDING'), ('132', 'OBMENKA_SUCCESS'), ('133', 'OBMENKA_FAIL');
 */
