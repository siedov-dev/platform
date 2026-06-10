<?php

namespace App\PaymentsAPI\ChatlPay;

use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
/**
 * @docs https://docs.chatlpay.com/
 */
final class ChatlPay
{
    private string $url = "https://partner-api.chatlpay.com";
    private string $test_url = "https://partner-api.platipays.com";
    private string $api_key = "e967a606060909ed4bac1e5367c7dd382a21b1ca4e339012087008a0a04716c0bfca03cec64090ce99a4bc3fbbd870fde9607dc0b8c1f4033f3da979c901a33df3ce0b7ee82355efffe239935c6bcff4a549b42092bd47a715c46df0716441134e83f45b6a118356f81aae5f504c0c6c8ed98dae41e0d548ed0fd2ae6b6aded9";
    private string $key = "f70f125c5f5bafbd69317ea19b2cf40410fb778b577a4eb27555aabaaf52c4cc23913f17b1f51c1efc7c99173c69678fead74067fcf136b73696d9a1cc545cb3b18b7e9fdae3732edd8a8cdb02865c5dec35dd60c0bd0916013bd3b7a7e8b817929a35adf52ed1858f263aa61d06dd89cc7c8fc7aac236abe4fda792ca2e90b398674bd8932bfce44cb613f0ae326de2a0d1124747c6aa15f8184b9cb36e3e9e2bd3560d6f080e08aeb30467bfe164c60147973f1a4c60f91f3767c43d91b61b1b41013a43de2b08476342f23e4155eaf7f7f7b661946e6aacb9fde4fa90dae52ca40897fa5c3795b55da3e0d7b627d7d9fb87f8c3d51e9375055a2603a42622";

    private string $test_api_key = "a617cd24987b9a337d51c2c60ece72f403aa3c17556ffb4fad10b7a48c5a4ee2550c983ebefb251846dc14586ac8c8731cbd7fe3391ffccf63fc195b85fdccb783933e765ea3fb0a1acf46788395c267bb35154197082ac5fa0a26207e35210da82cff4cf9617c46f5beba0cce984f81aaea64bbedb00c8b33af20fe14bab0a5";
    private string $test_secret = "f9481d297192b88d85f539503a4b12d27ed02d6ea821c95eb985a6d6c28a14b915a07680919fc369dae575e43f08d53ea423b1caa1d73f1d73ab8c6c39a13c8d253eb4be3c28572a4134bbbd631fca8eb6cda42908335f99c583ad9a18560a2949edfe0ec2baa5483c27b1310b43aba5d75ad0908d5e7d90350d7be42f535b4e286d92fe0eb4e1b6321aa521aab993577effce706616393ac7d0d12ac766d1db5218e77496e80a649c9b2de34c4570e1a34d57cc9aeb2f0b413d8c6a1008c9850b77c5430777f317b27915749b42f9b9480a7e82bd4df9739661bd1a8c6dc804878d62ee3440ff2036c0f20f9384339d36f05f4990b573375528659d53f64338";

    private $private_key;
    private string $unique = "";

    public function __construct(bool $is_test = false)
    {
        if ($is_test) {
            $this->api_key = $this->test_api_key;
            $this->key = $this->test_secret;
            $this->url = $this->test_url;
        }
    }

    public function depositForm(string $tr_id, float $amount, $currency, string $user_id)
    {
        $method = "/payment/deposit/form";
        $data = [
            'client_order_id' => $tr_id,
            'user_id' => $user_id,
            'amount' => $amount,
            'comment' => 'Kraken Deposit',
            'expire' => 900,
            'user_ip' => '5.61.51.246',
            'currency_id' => 1, //UAH
            'callback' => 'https://krakenfn.com/api/pay/chatl?'. http_build_query([
                    't' => $tr_id,
                    'a' => $amount,
                    'c' => hash_hmac('sha256', $tr_id.$amount, $this->key),
                ]),
        ];

        return $this->query($method, 'POST', $data);
    }

    public function payout(string $tr_id, string $user_id, float $amount, string $currency, string $holder, string $card_number)
    {
        $method = "/payment/withdraw";
        $data = [
            'client_order_id' => $tr_id,
            'user_id' => $user_id,
            'amount' => $amount,
            'comment' => 'Kraken payout',
                'user_ip' => '5.61.51.246',
            'currency_id' => 1, //UAH
            'callback' => 'https://krakenfn.com/api/pay/chatl?'. http_build_query([
                    't' => $tr_id,
                    'a' => $amount,
                    'c' => hash_hmac('sha256', $tr_id.$amount, $this->key),
                ]),
            'full_name' => $holder,
            'card_number' => $card_number,
        ];

        return $this->query($method, 'POST', $data);
    }

    public function paymentStatus(string $id)
    {
        $method = "/payment/details";
        $data = [
            'bill_id' => $id,
        ];

        return $this->query($method, 'POST', $data);
    }

    public function preAuth(MUser $user)
    {
        $private_key = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1']);

        // Получение публичного ключа из приватного
        $publicKey = openssl_pkey_get_details($private_key)['key'];

        // Преобразование PEM в DER
        $public_key_base64 = $this->panToDer($publicKey);

        $this->unique = uniqid();

        $this->private_key = $private_key;

        $method = "/quasi/preauth";
        $data = [
            'unique' => $this->unique,
            'user_id' => strval($user->getId()),
            'user_ip' => request()->ip(),
            'ps' => $public_key_base64,
        ];

        return $this->query($method, 'POST', $data);
    }

    public function asyncPay(PayoutTransaction $tr, MUser $user, $requisites, $shared_key)
    {
        $card_data = [
            'pan' => $requisites['cardNumber'],
            'date' => $requisites['month'].'/20'.$requisites['year'],
            'name' => $requisites['card_holder'],
            'cvv' => strval($requisites['cvc']),
            'amount' => floatval($tr->getSum()),
            'currency_id' => 1,
            'callback' => 'https://krakenfn.com/api/pay/chatl?'. http_build_query([
                    't' => $tr->getId(),
                    'a' => $tr->getSum(),
                    'c' => hash_hmac('sha256', $tr->getId().$tr->getSum(), $this->key),
                ]),
            'return_url' => route('landing', [], true),
            'user_id' => strval($user->getId()),
        ];
        Log::channel('pay')->info('$card_data:'.var_export($card_data, true));

        $encrypted = $this->encryptData(json_encode($card_data), $shared_key);
        Log::channel('pay')->info('encrypted:'.$encrypted);

        $method = "/quasi/async_pay";
        $data = [
            'unique' => $this->unique,
            'user_id' => strval($user->getId()),
            'dt' => $encrypted,
        ];

//        Log::channel('pay')->info('decrypted:'. $this->decryptData($encrypted, $shared_key));

        return $this->query($method, 'POST', $data);
    }

    public function quasiDetails($bill_id)
    {

        $method = "/quasi/details";
        $data = [
            'unique' => $this->unique,
            'bill_id' => $bill_id,
        ];

        return $this->query($method, 'POST', $data);
    }

    public function createSharedKey($outer_key)
    {
        $remote_public_key = $this->derToPan($outer_key);

        // Экспорт приватного ключа для дальнейшего использования
        openssl_pkey_export($this->private_key, $private_key_pem);

        // Генерация общего ключа шифрования
//        $shared_key = openssl_dh_compute_key($remote_public_key, openssl_pkey_get_private($private_key_pem));
        $shared_key = openssl_pkey_derive(openssl_pkey_get_public($remote_public_key), openssl_pkey_get_private($private_key_pem));

        return $shared_key;
    }

//    public function encryptData2(string $data, $shared_key, $iv = null)
//    {
//        $iv = $iv?? openssl_random_pseudo_bytes(16);
//        $aes = new AES('cfb');
//        $aes->setKey($shared_key);
//        $aes->setIV($iv);
//        $ct = $aes->encrypt($data);
//
//        $encrypted_data = $iv . $ct;
//        $encrypted_data_base64 = base64_encode($encrypted_data);
//
//        return $encrypted_data_base64;
//    }

    public function encryptData(string $data, $shared_key, $iv = null)
    {
        // Генерация вектора инициализации (IV)
        $iv = $iv?? openssl_random_pseudo_bytes(16);

        // Общий секретный ключ, полученный в результате обмена ключами (для примера используется случайный ключ)
//        $shared_secret = openssl_random_pseudo_bytes(32); // Замените на фактически полученный общий секрет

        // Шифрование данных с использованием AES-256-CFB
        $ciphertext = openssl_encrypt($data, 'aes-256-cfb', $shared_key, OPENSSL_RAW_DATA, $iv);

        // Конкатенация вектора инициализации и зашифрованных данных
        $encrypted_data = $iv . $ciphertext;

        // Кодирование в base64
        $encrypted_data_base64 = base64_encode($encrypted_data);

        return $encrypted_data_base64;
    }

    public function decryptData($encoded_data, $shared_key)
    {
        $encrypted_data_with_iv = base64_decode($encoded_data);

        $iv = substr($encrypted_data_with_iv, 0, 16);
        $encrypted_data = substr($encrypted_data_with_iv, 16);

        $decrypted_data = openssl_decrypt($encrypted_data, 'aes-256-cfb', $shared_key, OPENSSL_RAW_DATA, $iv);

        return $decrypted_data;
    }

    public function panToDer($publicKey)
    {
        // Преобразование PEM в DER
        $der = str_replace(['-----BEGIN PUBLIC KEY-----', '-----END PUBLIC KEY-----', "\n", "\r"], '', $publicKey);
        return $der;
    }

    public function derToPan($outer_key)
    {
        $pem = chunk_split($outer_key, 64, "\n");
        $pem = "-----BEGIN PUBLIC KEY-----\n".$pem."-----END PUBLIC KEY-----\n";
        return $pem;
    }

    private function sign(string $data)
    {
        return hash_hmac('sha512', $data, $this->key);
    }

    private function checkSign(array $data): bool
    {
        return hash_hmac('sha256', $data['t'].$data['a'], $this->key) === $data['c'];
    }

    private function query(string $method, string $httpMethod, array $data = [])
    {
        $url = $this->url . $method;

        $ch = curl_init();

        $payload = json_encode($data, JSON_FORCE_OBJECT);

        $sign = $this->sign($payload);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'APIKEY: ' . $this->api_key,
            'Signature: ' . $sign,
        ]);

        if ( strtolower($httpMethod) === 'post') {

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
            }
        }

        Log::channel('pay')->info(__CLASS__.' request: '.$url);
        Log::channel('pay')->info(var_export($data, true));
        Log::channel('pay')->info(__CLASS__.' request body: '.$url);
        Log::channel('pay')->info($payload);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        $json = curl_exec($ch);
        $err = curl_error($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $resp = json_decode($json, true);

        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);

        Log::channel('pay')->info('curl verbose');
        Log::channel('pay')->info(htmlspecialchars($verboseLog));

        if ($err) {
            Log::channel('pay')->error($err);
        }

        Log::channel('pay')->info(__CLASS__.' resp:');
        Log::channel('pay')->info('code:'.$httpcode);
        Log::channel('pay')->info($json);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__.' callback deposit request');
        Log::channel('pay')->info(var_export($request->getUri(), true));
        Log::channel('pay')->info(var_export($request->getContent(), true));
        Log::channel('pay')->info(var_export($request->all(), true));
//        $api = new CrossPay();

        $data = $request->all();

        if (!isset($data['t'])) {
            Log::channel('pay')->error('t field not set');
            return response('', 500);
        }

        $api = new ChatlPay();

        if (!$api->checkSign($data)) {
            Log::channel('pay')->error('wrong hash');
            return response('wrong hash', 500);
        }

        Log::channel('pay')->info('Start processing ChatlPay transaction with id ' . $data['t']);

        /** @var PayoutTransaction $transaction */
        $transaction = PayoutTransaction::where('id', $data['t'])
            ->whereIn('status_id', [
                PayoutTransaction::STATUS_CHATLPAY_PENDING,
                PayoutTransaction::STATUS_CHATLPAY_FAIL,
                ])
            ->first();

        if ($transaction->status_id === PayoutTransaction::STATUS_CHATLPAY_FAIL) Log::channel('pay')
            ->info(__CLASS__." transaction was in fail state");

        if ($transaction === null) {
            if (PayoutTransaction::where('id', $data['t'])
                    ->where('status_id', PayoutTransaction::STATUS_CHATLPAY_SUCCESS)
                    ->first() !== null) {
                Log::channel('pay')->error('Transaction with id ' .$data['t'] . ' is already complete found. Request: ' . PHP_EOL . var_export($request->all(), true));
                return response('ok', 200);
            }
            Log::channel('pay')->error('Transaction with id ' . $data['t'] . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
            return response('transaction not found', 404);
        }

        // Find user which create this transaction.
        /** @var MUser $user $user */
        $user = MUser::where('id', $transaction->user_id)->first();

        if ($user === null) {
            Log::channel('pay')->error('User of the transaction is not found.');
            return response('user not found', 500);
        }

        $status = $api->paymentStatus($transaction->o_tr_id);

        if (empty($status)) {
            Log::channel('pay')->error('Check status error'.var_export($status, true));
            return response('Check status error', 500);
        }

        if ($status['order_type'] !== 'deposit') {
            return 'ok';
        }

        if ($status['status'] === 'Failed') {
            Log::channel('pay')->error('status:' . $status['status']);
            $transaction->status_id = PayoutTransaction::STATUS_CHATLPAY_FAIL;
            $transaction->coment = $status['status']??' event error';
            $transaction->save();
            return 'ok';
        }

        if ($status['status'] !== 'Success') {
            return 'ok';
        }

        $amount = $status['amount'];

        $currency = UserWallet::CURRENCY_UAH;
        $transaction->fee = $status['fee']?? 0;

        Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_CHATLPAY_SUCCESS, $currency);

        return response('ok', 200);
    }

//    public function test()
//    {
//        // Генерация закрытого и открытого ключей
//        $private_key = EC::createKey('secp256r1');
//
//        $public_key = $private_key->getPublicKey();
//        $public_key_pan = $public_key->toString('PKCS8');
//        $public_key_base64 = $this->panToDer($public_key_pan);
//
//        $unique = uuid_create();
//
//        // Данные для отправки
//        $data = [
//            "unique" => $unique,
//            "user_id" => "169113***eeeeee",
//            "user_ip" => "127.0.0.1",
//            "ps" => $public_key_base64
//        ];
//
//        $response_data = $this->query('/quasi/preauth', 'post', $data);
//        $server_ps = $response_data['server_ps'] ?? null;
//
//        echo "Server PS: {$server_ps}" . PHP_EOL;
//        echo "===========================" . PHP_EOL;
//
//        $public_key_server_bytes = base64_decode($server_ps);
//        $public_key_server = PublicKeyLoader::load($public_key_server_bytes);
//
//        // Вычисление общего секрета с использованием ECDH
//        $shared_secret = DH::computeSecret($private_key, $public_key_server);
//
//        echo "Shared Secret: " . bin2hex($shared_secret) . PHP_EOL;
//
//        // Данные для шифрования
//        $data_to_encrypt = [
//            "pan" => "4111111111111111",
//            "date" => "03/2099",
//            "name" => "Name LastName",
//            "cvv" => "000",
//            "amount" => 511.16,
//            "currency_id" => 1,
//            "callback" => "https://webhook.site/",
//            "return_url" => "https://google.com/aaaa",
//            "user_id" => "333333333333",
//        ];
//        $data_bytes = json_encode($data_to_encrypt);
//
//        $iv = openssl_random_pseudo_bytes(16);
//        $aes = new AES('cfb');
//        $aes->setKey($shared_secret);
//        $aes->setIV($iv);
//        $ct = $aes->encrypt($data_bytes);
//
//        $encrypted_data = $iv . $ct;
//        $encrypted_data_base64 = base64_encode($encrypted_data);
//
//        echo "Encrypted Data: {$encrypted_data_base64}" . PHP_EOL;
//
//        $data2 = [
//            "unique" => $unique,
//            "dt" => $encrypted_data_base64
//        ];
//
//        $response_data2 = $this->query('/quasi/async_pay', 'post', $data2);
//        print_r($response_data2);
//        echo "===========================" . PHP_EOL;
//
//    }
}
