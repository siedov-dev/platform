<?php

namespace App\PaymentsAPI\FreeKassa;

use App\Models\PaySystemsConfig;
use App\Payin\Payin;
use Illuminate\Support\Facades\Log;

/**
 * @see https://www.fkwallet.ru/docs
 */
class FkWalletApi
{
    protected string $wallet_id = "F102915768";
    protected string $wallet_key = "A9147BE3FD601C12D41E5FF2C08AE8C4";

    /** @var bool $is_error */
    public $is_error = false;

    /** @var string $error_descr */
    public $error_descr = "";

    public function __construct($wallet_id = null, $wallet_key = null)
    {
        $conf = PaySystemsConfig::getSystemConfig(Payin::TYPE_FREEKASSA);
        $this->wallet_id = $conf['wallet_id'] ?? "F102915768";
        $this->wallet_key = $conf['wallet_key'] ?? "A9147BE3FD601C12D41E5FF2C08AE8C4";

        if($wallet_id) $this->wallet_id = $wallet_id;
        if($wallet_key) $this->wallet_key = $wallet_key;
    }

    public function getBalance($curr_name)
    {
        $sign = md5($this->wallet_id.$this->wallet_key);
        $data = [
            'wallet_id'=> $this->wallet_id,
            'sign'=> $sign,
            'action'=> 'get_balance',
        ];

        $result = $this->query($data);

        return $result['data'][$curr_name]?? false;
    }

    public function getProviders()
    {
        $sign = md5($this->wallet_id.$this->wallet_key);
        $data = [
            'wallet_id'=> $this->wallet_id,
            'sign'=> $sign,
            'action'=> 'providers',
        ];

        $result = $this->query($data);
        return $result;
    }

    public function payout(string $order_id, float $amount, $card_number, $service_id)
    {
        $data = array(
            'order_id' => $order_id,
            'wallet_id'=> $this->wallet_id,
            'purse'=> $card_number,
            'amount'=> $amount,
            'desc'=> 'Krakencassino payout',
            'currency'=> $service_id,
            'sign'=> md5($this->wallet_id.$service_id.$amount.$card_number.$this->wallet_key),
            'action'=> 'cashout',
            'disable_exchange' => 1,
            'check_duplicate' => 1,
        );

        $result = $this->query($data);

        return $result['data']?? false;
    }

    public function getPayoutStatus($transaction_id)
    {
        $data = array(
            'wallet_id'=> $this->wallet_id,
            'payment_id'=> $transaction_id,
            'sign'=>md5($this->wallet_id.$transaction_id.$this->wallet_key),
            'action'=>'get_payment_status',
        );

        $result = $this->query($data);

        return $result['data']?? false;
    }

    public function getSystemId($method)
    {
        $methods = [
            'FK.fkrub' => 133,
            'FK.qiwi' => 63,
            'FK.ym' => 45,
            'FK.visarub' => 94,
            'FK.tele2' => 132,
            'FK.bl' => 83,
            'FK.mts' => 84,
            'FK.megafon' => 82,
            'FK.payerrub' => 114,
            'FK.advcash' => 150,
        ];
        return $methods[$method];
    }

    public function query($data)
    {
        $this->is_error = false;
        $this->error_descr = '';

        Log::channel('pay')->info('FkWallet query:'.var_export($data, true));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.fkwallet.ru/api_v1.php');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = trim(curl_exec($ch));
        $c_errors = curl_error($ch);
        curl_close($ch);

        Log::channel('pay')->info('FreeKassa response:'.$response);

        if(!$response){
            Log::channel('pay')->error('Can`t connect to FreeKassa payment system');
            $this->is_error = true;
            $this->error_descr = "Can`t connect to FreeKassa payment system";
            return false;
        }

        $resp = json_decode($response, true);

        if (!is_array($resp)) {
            Log::channel('pay')->error('response error');
            $this->is_error = true;
            $this->error_descr = "response error";
            return false;
        }

        if($resp['status'] == 'error'){
            Log::channel('pay')->error('FreeKassa error');
            Log::channel('pay')->error(var_export($resp, true));
            $this->is_error = true;
            $this->error_descr = $resp['desrc']??'unknown error';
            return false;
        }

        return $resp;
    }
}
