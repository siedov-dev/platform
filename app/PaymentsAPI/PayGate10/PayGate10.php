<?php

namespace App\PaymentsAPI\PayGate10;

use App\Models\PaySystemsConfig;
use App\Payin\Payin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * PayGate10 payment api
 */
final class PayGate10
{
    private string $mid_code = "423";
    private string $mid_secret = "77e3ae1c-fef7-46ac-abfd-d97ae5f48811";
    private string $url = "https://api.paygate10.com/Service.svc/rest/";

    public function __construct()
    {
        $conf = PaySystemsConfig::getSystemConfig(Payin::TYPE_PAYGATE);
        $this->mid_code = $conf['mid_code'] ?? "2d1844634a8f4c5e9abe";
        $this->mid_secret = $conf['mid_secret'] ?? "77e3ae1c-fef7-46ac-abfd-d97ae5f48811";
    }

    public function deposit(int $tr_id, float $amount, string $method, array $requisites, string $currency = 'INR')
    {
//        $test_reqs = json_decode('{
//                        "firstname": "Steve",
//                        "lastname": "kuhl",
//                        "mobile": "1234567890",
//                        "email": "na@gmail.com",
//                        "address": "test",
//                        "city": "test",
//                        "state": "ts",
//                        "postcode": "1234",
//                        }', true);
//        $requisites = $test_reqs;

        $dec_amount = number_format($amount, 2, '.', '');

        $time = time();
        $data = array_merge([
            'midcode' => $this->mid_code,
            'timestamp' => $time,
            'currency' => $currency,
            'amount' => $dec_amount,
            'returnurl' => action('PayController@success'),
//            'callbackurl' => action('PayController@payGatePay'),
            'callbackurl' => "https://krakencassino.com/api/pay/paygate10",
            'encrypthash' => $this->depositHash($amount, $time),
            'requesttype' => 'deposit',
            'countrycode' => 'IN',
            'requestfor' => $method,
            'reference1' => $tr_id,
        ], $requisites);

//        dump($data);
        return $this->query('PaymentRequest', 'post', $data);
    }

    public function payout(string $tr_id, float $amount, string $method, array $requisites, string $currency = 'INR')
    {
//        $test_reqs = json_decode('{
//                        "accountname": "Test",
//                        "accountnumber": "132456789",
//                        "bankname": "apnaBank",
//                        "bankifsc": "HDFC0000052",
//                        "bankbranch": "Mumbai",
//                        "bankaddress": "Mumbai"
//                        }', true);
//        $requisites = $test_reqs;

        $dec_amount = number_format($amount, 2, '.', '');

        $time = time();
        $data = array_merge($requisites, [
//            "username" => Auth::user()->login,
            'requestcode' => $tr_id,
            'midcode' => $this->mid_code,
            'timestamp' => $time,
            'currency' => $currency,
            'amount' => $dec_amount,
            'requesttype' => 'withdrawal',
            'requestfor' => $method,
            'encrypthash' => $this->payoutHash($amount, $time),
            'callbackurl' => "https://krakencassino.com/api/pay/paygate10",
            "countrycode" => "IN",
        ]);

//        dump($data);
        return $this->query('PaymentRequest', 'post', $data);
    }

    public function getPayout(string $id)
    {
        $data = [
            'payoutid' => $id,
            'apikey' => $this->mid_code,
            'hash' => md5($this->mid_secret . $this->mid_code. $id)
        ];

        return $this->query('v2/GetPayout', 'post', $data);
    }

    public function checkSign($data)
    {
        return $this->callbackHash($data['amount'], $data['txid']) === $data['hash'];
    }

    public function callbackHash(float $amount, string $txid)
    {
        $dec_amount = number_format($amount, 2, '.', '');
        return md5($this->mid_code . $txid . $dec_amount . $this->mid_secret);
    }

    private function depositHash(float $amount, int $time): string
    {
        $dec_amount = number_format($amount, 2, '.', '');
        return md5($this->mid_code . $time . $dec_amount . $this->mid_secret);
    }

    private function payoutHash(float $amount, int $time): string
    {
        $dec_amount = number_format($amount, 2, '.', '');
        return md5($this->mid_secret . $time . $dec_amount);
    }

    private function query($method, $httpMethod, array $data = [])
    {
        $url = $this->url . $method;

        $ch = curl_init();
        if ( strtolower($httpMethod) === 'post') {

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        } else {

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_ENCODING, "");
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
            }
        }

        Log::channel('pay')->info('PayGate10 request: '.$url);
        Log::channel('pay')->info(json_encode($data));
        Log::channel('pay')->info(var_export($data, true));


        curl_setopt($ch, CURLOPT_HEADER, false);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

        $text = curl_exec($ch);
        $err = curl_error($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $resp = json_decode($text, true);
        if ($err) {
            Log::channel('pay')->error($err);
        }

        Log::channel('pay')->info('PayGate10 resp:');
        Log::channel('pay')->info('code:'.$httpcode);
        Log::channel('pay')->info($text);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }
}
