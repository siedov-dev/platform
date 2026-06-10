<?php


namespace App\PaymentsAPI;


use App\Models\PaySystemsConfig;
use App\Payin\Payin;
use Illuminate\Support\Facades\Log;

/**
 * Class Payeer
 * @package App\PaymentsAPI
 * @see https://payeerru.docs.apiary.io/#reference/0
 */
class Payeer
{
    private static $password = 'WPraJsFTcvqTMKOH';
    private static $id_merchant = '1018356969';//API Id
    private static $account_id = 'P38809844';
    private static $shop_id = 'id_793594675';

    public function __construct()
    {
        $conf = PaySystemsConfig::getSystemConfig(Payin::TYPE_PIASTRIX);
        $this->password = $conf['password'] ?? 'WPraJsFTcvqTMKOH';
        $this->id_merchant = $conf['id_merchant'] ?? '1018356969';
        $this->account_id = $conf['account_id'] ?? 'P38809844';
        $this->shop_id = $conf['shop_id'] ?? 'id_793594675';
    }

    /**
     * check credentials
     * @return mixed
     * @throws \Exception
     */
    public function checkAuth()
    {
        $resp = $this->send_query([
            'account' => self::$account_id,
            'apiId' => self::$id_merchant,
            'apiPass' => self::$password,
        ]);

        return $resp;
    }

    /**
     * get balance in specified currency
     * @param string $currency
     * @return object|null
     * @throws \Exception
     */
    public function getBalance(string $currency = 'RUB')
    {
        $resp = $this->send_query([
            'account' => self::$account_id,
            'apiId' => self::$id_merchant,
            'apiPass' => self::$password,
            'action' => 'getBalance',
        ]);

        if($resp && isset($resp['balance'][$currency]))
        return $resp['balance'][$currency];

        return null;
    }

    /**
     * check if Payeer account exists
     * @param string $account_No
     * @return bool
     * @throws \Exception
     */
    public function accountExists(string $account_No):bool
    {
        $resp = $this->send_query([
            'account' => self::$account_id,
            'apiId' => self::$id_merchant,
            'apiPass' => self::$password,
            'action' => 'checkUser',
            'user' => $account_No,
        ]);

        if($resp && empty($resp['errors'])) return true;

        Log::info('Payeer account check error:'.PHP_EOL.var_export($resp, true));

        return false;
    }

    /**
     * get exchange rates
     * @param string $output N - input exchange rate
                            Y - output exchange rates
     * @return array|null
     * @throws \Exception
     */
    public function getExchangeRate($output = 'Y')
    {
        $resp = $this->send_query([
            'account' => self::$account_id,
            'apiId' => self::$id_merchant,
            'apiPass' => self::$password,
            'action' => 'getExchangeRate',
            'output' => $output,
        ]);

        if($resp && empty($resp['errors'])) return $resp['rate'];

        return null;
    }

    /**
     * check if it is possible to transfer money to specified account
     * @param float $amount transfer sum
     * @param string $account
     * @param string $pay_system pay system code default 1136053 - Payeer
     *                          @see getPaySystems
     * @param string $curIn shop currency
     * @param string $curOut receiver currency
     * @param float $sumOut
     * @return bool
     * @throws \Exception
     */
    public function payoutCheck(float $amount, string $account, $pay_system = '1136053', $curIn = 'RUB', $curOut = 'RUB', float $sumOut = 0)
    {

        $query = [
            'account' => self::$account_id,
            'apiId' => self::$id_merchant,
            'apiPass' => self::$password,
            'action' => 'payoutChecking',
            'ps' => $pay_system,
            'sumIn' => $amount,
            'curIn' => $curIn,
            'curOut' => $curOut,
            'param_ACCOUNT_NUMBER' => $account,
        ];

        if($sumOut !== 0) $query['sumOut'] = $sumOut;

        $resp = $this->send_query($query);

        if($resp && empty($resp['errors'])) return true;

        return false;
    }

    /**
     * transfer money to Payeer account
     * @param float $amount
     * @param string $account
     * @param $comment
     * @param $referenceId
     * @param string $curIn
     * @param string $curOut
     * @param float $sumOut
     * @return bool
     * @throws \Exception
     */
    public function transfer(float $amount, string $account, $comment, $referenceId, float $sumOut = 0,  $curIn = 'RUB', $curOut = 'RUB')
    {
        $query = [
            'account' => self::$account_id,
            'apiId' => self::$id_merchant,
            'apiPass' => self::$password,
            'action' => 'transfer',
            'curIn' => $curIn,
            'sum' => $amount,
            'curOut' => $curOut,
            'to' => $account,
            'comment' => $comment,
            'referenceId' => $referenceId,
        ];

        if($sumOut !== 0) $query['sumOut'] = $sumOut;

        $resp = $this->send_query($query);

        if($resp && empty($resp['errors']) && $resp['success']){
            return $resp['historyId'];
        }

        Log::error('Payeer transfer error:'.PHP_EOL.var_export($resp, true));

        return false;
    }

    /**
     * get status information about payout transfer
     * @param string $ref_id id of payout transaction (referenceId in @see transfer)
     * @return bool|array
     * @throws \Exception
     */
    public function paymentDetails(string $ref_id)
    {
        $resp = $this->send_query([
            'account' => self::$account_id,
            'apiId' => self::$id_merchant,
            'apiPass' => self::$password,
            'action' => 'payoutDetails',
            'referenceId' => $ref_id,
        ]);

        if($resp && empty($resp['errors'])) return $resp['history'];

        Log::error('Payeer payment details error:'.PHP_EOL.var_export($resp, true));
        return false;
    }

    /**
     * get available pay systems
     * @return mixed|null
     * @throws \Exception
     */
    public function getPaySystems()
    {
        $resp = $this->send_query([
            'account' => self::$account_id,
            'apiId' => self::$id_merchant,
            'apiPass' => self::$password,
            'action' => 'getPaySystems',
        ]);

        if($resp && empty($resp['errors'])) return $resp['list'];

        return null;
    }

    /**
     * send query to Payeer api
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    private function send_query(array $data)
    {
        $json=json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, 'https://payeer.com/ajax/api/api.php');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        $response  = curl_exec($ch);

        curl_close($ch);

        if(!$response){
            Log::error('Can`t connect to Payeer payment system');
            return false;
        }

        $resp=json_decode($response, true);
        if($resp['auth_error'] == 0) return $resp;

        Log::error('Payeer api error:'.PHP_EOL.var_export($resp, true));
        return false;
    }
}
