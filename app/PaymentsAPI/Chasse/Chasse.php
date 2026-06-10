<?php

namespace App\PaymentsAPI\Chasse;

use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use App\ApplicationForPayment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\File;
use \Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use \Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * @deprecated
 */
class Chasse
{
    function __construct() {
        $this->clientCodeName = 'kraken';
        $this->publicKey = file_get_contents(dirname(__FILE__).'/pub_key.pem');
        $this->privateKey = file_get_contents(dirname(__FILE__).'/priv_key.pem');
        $this->testAddress = 'https://api.clients.sandbox.chasse.global';
        $this->mainAddress = 'https://api.clients.chasse.global';
        $this->test = false;
    }

    public function payin($transactionId, $amount, $currency, $requisites)
    {
        $createData = $this->payInCreate($transactionId, $amount, $requisites);

        if(isset($createData['errorData'])) return $createData;

        $this->payInPay($createData);

        return [
            'fee' => $createData['data']['fee']
        ];
    }

    public function payInPay($dataCreate){
        $method = '/v1/payment/in/pay';
        $api = $this->mainAddress;

        if($this->test) $api = $this->testAddress;

        $data = [
            'paymentId' => $dataCreate['data']['paymentId'],
            'cvvCode' => $dataCreate['cvv'],
        ];


        $token = JWT::encode($data, $this->privateKey, 'RS256');

        $q = [
            'auth' => [
                'clientCodeName' => $this->clientCodeName,
                'token' => $token
            ],
            'data' => $data
        ];


        $response = Http::acceptJson()->post($api.$method, $q);
        $resp = $response->json();

        dd($resp);

    }

    public function payInCreate($transactionId, $amount, $requisites){

        $method = '/v1/payment/in/create';
        $api = $this->mainAddress;

        if($this->test) $api = $this->testAddress;

        $data = [
            //'clientPublicKey' => $this->publicKey,
            'amount' => $amount*100,
            'externalId' => (string)$transactionId,
            'redirectUrl' => route('landing'),
            'mode3DS' => '2',
            'cardFrom' => [
                'pan' => $requisites['cardNumber'],
                'expiresAt' => '20'.$requisites['year'].'-'.$requisites['month'],
            ],
            'currency' => 'UAH',
            'type' => 'ECOM',
            'feeCalculationType' => 'INNER',
            'senderFullName' => $requisites['card_holder'],
            'data3DS2' => [
                'browserAcceptHeader' => 'application/json, text/plain, */*',
                'browserColorDepth' => '24',
                'browserIP' => '0.0.0.0',
                'browserLanguage' => app()->getLocale(),
                'browserScreenHeight' => '1080',
                'browserScreenWidth' => '1920',
                'browserTZ' => '-180',
                'browserUserAgent' => '-180',
                'browserJavaEnabled' => 'true',
                'windowWidth' => '1080',
                'windowHeight' => '1800',
            ]
        ];

        $token = JWT::encode($data, $this->privateKey, 'RS256');

        $q = [
            'auth' => [
                'clientCodeName' => $this->clientCodeName,
                'token' => $token
            ],
            'data' => $data
        ];


        $response = Http::acceptJson()->post($api.$method, $q);
        $resp = $response->json();

        $resp['cvv'] = $requisites['cvv'];

        return $resp;
    }


}
