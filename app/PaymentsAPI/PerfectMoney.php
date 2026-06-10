<?php


namespace App\PaymentsAPI;


use App\Models\PaySystemsConfig;
use App\Payin\Payin;
use Illuminate\Support\Facades\Log;

class PerfectMoney
{
    private $usd_account = 'U21637049';
    private $acc_name = 'krakencassino';
    private $alt_passphrase = '7x3t25p5eSxtAyIEoPGonrvdB';
    private $acc_id = 3973436;
    private $pass_phrase = '128821Tm2011--';

    public function __construct()
    {
        $conf = PaySystemsConfig::getSystemConfig(Payin::TYPE_PM);
        $this->acc_id = $conf['acc_id'] ?? 3973436;
        $this->usd_account = $conf['usd_account'] ?? 'U21637049';
        $this->pass_phrase = $conf['pass_phrase'] ?? '128821Tm2011--';
        $this->alt_passphrase = $conf['alt_passphrase'] ?? '7x3t25p5eSxtAyIEoPGonrvdB';
    }

    /**
     * get phrase hash
     * @return string
     */
    private function alt_phrase_hash()
    {
        return strtoupper(md5($this->alt_passphrase));
    }

    public function get_hash(array $data):string
    {
        $string=
            $data['PAYMENT_ID'].':'.$data['PAYEE_ACCOUNT'].':'.
            $data['PAYMENT_AMOUNT'].':'.$data['PAYMENT_UNITS'].':'.
            $data['PAYMENT_BATCH_NUM'].':'.
            $data['PAYER_ACCOUNT'].':'.$this->alt_phrase_hash().':'.
            $data['TIMESTAMPGMT'];

        return strtoupper(md5($string));
    }

    public function check_hash(array $post_data):bool
    {
        return $post_data['V2_HASH'] == $this->get_hash($post_data);
    }

    public function getBalance()
    {
        $data = [
            'AccountID' => $this->acc_id,
            'PassPhrase' => $this->pass_phrase,
        ];

        $resp = $this->send_query('balance', $data);

        if($resp !== false && !isset($resp['ERROR'])){
            if(!empty($resp) && array_key_exists($this->usd_account, $resp)){
                return floatval($resp[$this->usd_account]);
            }
        }

        return false;
    }

    public function getAccountName(string $account)
    {
        $data = [
            'AccountID' => $this->acc_id,
            'PassPhrase' => $this->pass_phrase,
            'Account' => $account,
        ];

        $resp = $this->send_query('acc_name', $data);

        if($resp !== false && !isset($resp['ERROR'])){
                return $resp;
        }

        return false;
    }

    public function transfer(float $amount, string $account, string $transaction_id)
    {

        $data = [
            'AccountID' => $this->acc_id,
            'PassPhrase' => $this->pass_phrase,
            'Payer_Account' => $this->usd_account,
            'Payee_Account' => $account,
            'Amount' => $amount,
//            'PAY_IN' => 1,
            'PAYMENT_ID' => $transaction_id
        ];

        return $this->send_query('confirm', $data);
    }

    /**
     * send query to PerfectMoney api
     * @param string $method
     * @param array $data
     * @return array|bool
     * @throws \Exception
     */
    private function send_query(string $method, array $data)
    {

        $url = "https://perfectmoney.com/acct/$method.asp";
        Log::info('PerfectMoney query: '.$url);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        $response  = curl_exec($ch);

        curl_close($ch);

        if(!$response){
            Log::error('Can`t connect to PerfectMoney payment system');
            return false;
        }

        if($method === 'acc_name') return $response;

        $result = null;
        // searching for hidden fields
        if(!preg_match_all("/<input name='(.*)' type='hidden' value='(.*)'>/", $response, $result, PREG_SET_ORDER)){
            Log::error('PerfectMoney api error:'. $response);
            return false;
        }

        Log::info('PerfectMoney api response:'. $response);

        $ar=[];
        foreach($result as $item){
            $key = $item[1];
            $ar[$key] = $item[2];
        }

        return $ar;
    }

    /*
     * success
   array:9 [▼
  "PAYEE_ACCOUNT" => "U21637049"
  "PAYMENT_AMOUNT" => "0.2"
  "PAYMENT_UNITS" => "USD"
  "PAYMENT_BATCH_NUM" => "315515196"
  "PAYMENT_ID" => "123456"
  "SUGGESTED_MEMO" => null
  "V2_HASH" => "4240B43665310D9B5824778D812C0E1D"
  "TIMESTAMPGMT" => "1590056660"
  "PAYER_ACCOUNT" => "U21637049"
]
     */

    /*
     * decline
     * array:6 [▼
  "PAYEE_ACCOUNT" => "U21637049"
  "PAYMENT_AMOUNT" => "0.7"
  "PAYMENT_UNITS" => "USD"
  "PAYMENT_ID" => "1488589"
  "SUGGESTED_MEMO" => null
  "PAYMENT_BATCH_NUM" => "0"
]

     */

    //AccountID=myaccount&PassPhrase=mypassword&Payer_Account=U987654&Payee_Account=U1234567&Amount=1&PAY_IN=1&PAYMENT_ID=1223'
}
