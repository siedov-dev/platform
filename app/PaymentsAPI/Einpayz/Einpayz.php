<?php

namespace App\PaymentsAPI\Einpayz;

use App\Models\PaySystemsConfig;
use App\Payin\Payin;
use Illuminate\Support\Facades\Log;

final class Einpayz
{
    private int $client_id = 50;
    private int $currency_id = 3;
    private int $country_id = 1;

    private string $private_key;
    private string $public_key;
    private string $url = 'https://apistage5.superpay.cloud/api/v4/methods/get';

    public function __construct()
    {
        $conf = PaySystemsConfig::getSystemConfig(Payin::TYPE_EINPAYZ);
        $this->private_key = $conf['private_key']?? trim(file_get_contents(__DIR__.'/private_key.pem'));
//        $this->public_key = trim(file_get_contents(__DIR__.'/public_key.pem'));
        $this->public_key = $this->getPubKey();
    }

    public function deposit($amount, $transaction_id, $p_method)
    {
        $method = '';
        $http_method = 'post';
        $payload = [
            'salt' => hash('sha256', time()),
            'requested_method' => $p_method, //'ALL_AVAILABLE',
            'transaction_type' => 1,
            'currency_id' => $this->currency_id,
            'country_id' => $this->country_id,
            'amount' => $amount,
            'client_id' => $this->client_id,
            'client_user_id' => 'demotestuser',
            'client_user_ip_address' => '5.61.51.246',//request()->ip(),
            'client_transaction_id' => $transaction_id,
            'timestamp' => time(),
            'client_public_key' => $this->public_key,
        ];
        $data = [
            'payload' => $payload,
        ];
        $data['signature'] = $this->sign($payload);

        return $this->query($method, $http_method, $data);

    }

    public function getPubKey()
    {
        $key = openssl_get_privatekey($this->private_key);
        $details = openssl_pkey_get_details($key);
        $public_key_pem = $details['key'];
        return $public_key_pem;
    }

    public function sign(array $data)
    {
        $key = openssl_get_privatekey($this->private_key);
        openssl_sign(json_encode($data, JSON_UNESCAPED_LINE_TERMINATORS|JSON_UNESCAPED_SLASHES), $sing, $key, OPENSSL_ALGO_SHA256);
        openssl_free_key($key);
        return base64_encode($sing);
    }

    public function checkSign(array $data)
    {
        $pub_key = $this->public_key;
        $payload = $data['payload'];
        $payload['pub_key'] = $pub_key;
        $payload_str = json_encode($payload, JSON_UNESCAPED_LINE_TERMINATORS|JSON_UNESCAPED_SLASHES);
//        dump($payload_str);
        return openssl_verify($payload_str, $data['signature'], $pub_key, "sha256WithRSAEncryption");
    }

    private function query($method, $httpMethod, array $data = [])
    {
        $url = $this->url . '/' . $method;
        Log::channel('pay')->info(__METHOD__.' query url:'.$url);
        Log::channel('pay')->info(__METHOD__.' query data:'.var_export($data, true));
        Log::channel('pay')->info(__METHOD__.' query data:'.json_encode($data, JSON_UNESCAPED_LINE_TERMINATORS|JSON_UNESCAPED_SLASHES));

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            ],
        );

        if ($httpMethod === 'post') {

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_LINE_TERMINATORS|JSON_UNESCAPED_SLASHES));

        } else {
            $url .= '?' . http_build_query($data);
        }


        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

//        curl_setopt($ch, CURLOPT_VERBOSE, true);
//        $verbose = fopen('php://temp', 'w+');
//        curl_setopt($ch, CURLOPT_STDERR, $verbose);


        $text = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        if ($err) {
            Log::error($err);
        }

        $resp = json_decode($text, true);


//        rewind($verbose);
//        $verboseLog = stream_get_contents($verbose);

//        Log::channel('pay')->info('curl verbose');
//        Log::channel('pay')->info(htmlspecialchars($verboseLog));


        Log::channel('pay')->info(__METHOD__.' query code:'.var_export($httpcode, true));
        Log::channel('pay')->info(__METHOD__.' query resp:'.var_export($text, true));
        Log::channel('pay')->info(__METHOD__.' query resp:'.var_export($resp, true));
        return $resp;
    }

    public function genKeys()
    {
        //data you want to sign
        $data = 'my data';

        //create new private and public key
        $new_key_pair = openssl_pkey_new(array(
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ));
        openssl_pkey_export($new_key_pair, $private_key_pem);

        $details = openssl_pkey_get_details($new_key_pair);
        $public_key_pem = $details['key'];

        //create signature
        openssl_sign($data, $signature, $private_key_pem, OPENSSL_ALGO_SHA256);

        //save for later
        file_put_contents('private_key.pem', $private_key_pem);
        file_put_contents('public_key.pem', $public_key_pem);
//        file_put_contents('signature.dat', $signature);

        //verify signature
        $r = openssl_verify($data, $signature, $public_key_pem, "sha256WithRSAEncryption");
        var_dump($r);
    }

    public function genCert()
    {
        $configParams = null;
        $privkey = openssl_get_privatekey($this->private_key);
        // generates a certificate signing request
        $csr = openssl_csr_new([], $privkey, $configParams);
        if ($csr === FALSE){
            return FALSE;
        }
        // This creates a self-signed cert that is valid for $duration days
        $sscert = openssl_csr_sign($csr, null, $privkey, 360, $configParams);
        if ($sscert === FALSE){
            return FALSE;
        }
        // expport the certificate and the private key
        openssl_x509_export($sscert, $certout);
        openssl_pkey_export($privkey, $pkout, '', $configParams);

        $file = 'cert';

        file_put_contents($file.".cer", $certout);
        file_put_contents($file.".pem", $pkout);

        $result = array('cer'=>$certout, 'pem'=>$pkout, 'file'=>$file);

        // Gets any errors that occurred here
        $allErrors = '';
        while (($e = openssl_error_string()) !== false) {
            $allErrors .= $e . "\n";
        }
        if ($allErrors != ''){
            $aErrs = explode("\n", $allErrors);
            foreach($aErrs as $err)
                dump($err);
        }

        return $result;
    }
}
