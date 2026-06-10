<?php

namespace App\PaymentsAPI\Inwizo;

use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class Inwizo
{
//    private string $_account = 'ACC013161';
//    private string $_key_1 = 'ada1f730-3e17-a50c-9997-1f6f2af39902';
//    private string $_key_2 = '0|u9$M*FpdW$||4xzr#|';
//    private string $_url = "https://securepayments.test.inwizo.com/api/";
    private string $_account = 'ACC030283';
    private string $_key_1 = '83bb701a-0595-6329-8080-edc60c5511e1';
    private string $_key_2 = '5eNZ3VfH0ePTmI7yeDAV';
    private string $_url = "https://pay.vd8b19m.com/api/";

    public function __construct(string $method = '', ?string $comment = "")
    {
        if ($comment !== 'trusted') {
            $this->_account = 'ACC030279';
            $this->_key_1 = "94ecd5e5-dce7-81bb-edb2-1f3907d7c5f7";
            $this->_key_2 = "4kal1CR9PQTWvBEfSshm";
        }

        if ($method === 'ext') {
            $this->_account = 'ACC029048';
            $this->_key_1 = "3a2f282b-0c18-b625-7d6a-c24dcc52d790";
            $this->_key_2 = "ZTbltoZBacnvnRiUHlxz";
        }
        if ($method === 'ext-KZT') {
            $this->_account = 'ACC030264';
            $this->_key_1 = "1f89904b-7ca4-7df8-8e80-b413531251ff";
            $this->_key_2 = "qXpXyqkFxI4kEsbymw1j";
        }
    }

    public function depositExternal(float $amount, int $currency_id, string $method, string $tr_id, array $requisites): string
    {
//        $endpoint = "https://fin.4g12hs.com/api/payment/external";
        $endpoint = $this->_url . 'payment/external';
        $data = [
            'amount' => $amount,
            'amountcurr' => UserWallet::currencyName($currency_id),
            'number' => $tr_id,
            'description' => urlencode('Krakencassino'),
            'trtype' => 1,
            'account' => $this->_account,
            'email' => $requisites['email']??'',
            'phone' => $requisites['phone']??'',
//            'currency' => 'MBC',
//            'backURL' => route('pay.success'),
//            'ip_address' => \request()->ip(),
            'ip_address' => '5.45.66.203',
            'user_agent' => \request()->userAgent(),
            'accept_language' => $_SERVER['HTTP_ACCEPT_LANGUAGE']?? 'ru',
        ];

        $data['signature'] = $this->sign($data);

        return view('pay.inwizo-form', ['url' => $endpoint, 'data' => $data])->render();
    }

    public function deposit(float $amount, int $currency_id, string $method, string $tr_id, array $requisites): string
    {
        $endpoint = 'payment/start';
        $data = [
            'amount' => $amount,
            'amountcurr' => UserWallet::currencyName($currency_id),
            'currency' => $method,
            'number' => $tr_id,
            'description' => urlencode('Krakencassino deposit'),
            'trtype' => 1,
            'account' => $this->_account,
            'email' => $requisites['email']??'',
            'phone' => $requisites['phone']??'',
            'backURL' => route('pay.success'),
        ];

        $data['signature'] = $this->sign($data);

        return view('pay.inwizo-form', ['url' => $this->_url.$endpoint, 'data' => $data])->render();
    }

    private function sign(array $data = []): string
    {
        $fields = [
            'amount' => null,
            'amountcurr' => null,
            'currency' => null,
            'number' => null,
            'description' => null,
            'trtype' => null,
            'account' => null,
            'BIN' => null,
            'LAST4' => null,
            'expmonth' => null,
            'expyear' => null,
            'cardholder' => null,
            'paytoken' => null,
            'backURL' => null,
            'cf1' => null,
            'cf2' => null,
            'cf3' => null,
        ];

        $res = array_intersect_key($fields, $data);
        foreach ($res as $k => $V) {
            $res[$k] = $data[$k];
        }
        $res['key1'] = $this->_key_1;
        $res['key2'] = $this->_key_2;
        $hash_str = implode(':', $res);
        Log::channel('pay')->info('hash string:'.$hash_str);
//        return md5($hash_str);
        return strtoupper(hash_hmac('sha256', $hash_str, $this->_key_1.$this->_key_2));
    }

    private function signResponse(array $data = []): string
    {
        $fields = [
            'amount' => null,
            'amountcurr' => null,
            'currency' => null,
            'number' => null,
            'description' => null,
            'trtype' => null,
            'account' => null,
            'cf1' => null,
            'cf2' => null,
            'cf3' => null,
            'paytoken' => null,
            'backURL' => null,
            'transID' => null,
            'datetime' => null,
        ];

        $res = array_intersect_key($fields, $data);
        foreach ($res as $k => $V) {
            $res[$k] = $data[$k];
        }
        $res['key1'] = $this->_key_1;
        $res['key2'] = $this->_key_2;
        $hash_str = implode(':', $res);
        Log::channel('pay')->info('hash string:'.$hash_str);
//        return md5($hash_str);
        return strtoupper(hash_hmac('sha256', $hash_str, $this->_key_1.$this->_key_2));
    }

    public function checkSign($data): bool
    {
        return $data['signature'] === $this->signResponse($data);
    }

    public function depositInfo(string $tr_id)
    {
        $method = 'payment/operate';
        $data = [
            'opertype' => 'check',
            'account' => $this->_account,
            'number' => $tr_id,
        ];
        $sign_data = json_decode(json_encode($data), true);
        $sign_data['key1'] = $this->_key_1;
        $sign_data['key2'] = $this->_key_2;
//        $data['appinfo'] = 0;
        $data['signature'] = strtoupper(hash_hmac('sha256', implode(':', $sign_data), $this->_key_1.$this->_key_2));        return $this->query($method, 'post', $data);
    }

    public function payout(string $card_number, float $amount, string $currency, string $tr_id)
    {
        $method = 'payout/execute';
        $data = [
            'nonce' => bin2hex(openssl_random_pseudo_bytes(32)),
            'account' => $this->_account,
            'operator' => '0000',
//            'operator' => '12274', test
            'params' => $card_number,
            'amount' => $amount,
            'amountcurr' => $currency,
            'number' => $tr_id,
        ];
        $sign_data = json_decode(json_encode($data), true);
        $sign_data['params'] = substr($card_number, 0, 6). '******'. substr($card_number, -4, 4);
        $sign_data['key1'] = $this->_key_1;
        $sign_data['key2'] = $this->_key_2;
        Log::channel('payout')->info('sign data:'.var_export($sign_data, true));
        $data['signature'] = strtoupper(hash_hmac('sha256', implode(':', $sign_data), $this->_key_1.$this->_key_2));
        Log::channel('payout')->info('payout data:'.var_export($data, true));
        return $this->query($method, 'post', $data);
    }

    public function payoutInfo(string $tr_id)
    {
        $method = 'payout/status';
        $data = [
            'nonce' => bin2hex(openssl_random_pseudo_bytes(32)),
            'account' => $this->_account,
            'number' => $tr_id,
        ];
        $sign_data = json_decode(json_encode($data), true);
        $sign_data['transID'] = '';
        $sign_data['key1'] = $this->_key_1;
        $sign_data['key2'] = $this->_key_2;
        $data['signature'] = strtoupper(hash_hmac('sha256', implode(':', $sign_data), $this->_key_1.$this->_key_2));
        return $this->query($method, 'post', $data);
    }

    public function payoutInfoTr(string $tr_id)
    {
        $method = 'payout/status';
        $data = [
            'nonce' => bin2hex(openssl_random_pseudo_bytes(32)),
            'account' => $this->_account,
            'number' => '',
            'transID' => $tr_id,
        ];
        $sign_data = json_decode(json_encode($data), true);
        $sign_data['number'] = '';
        $sign_data['key1'] = $this->_key_1;
        $sign_data['key2'] = $this->_key_2;
        $data['signature'] = strtoupper(hash_hmac('sha256', implode(':', $sign_data), $this->_key_1.$this->_key_2));
        return $this->query($method, 'post', $data);
    }

    public function balance()
    {
        $method = 'payout/balance';
        $data = [
            'nonce' => bin2hex(openssl_random_pseudo_bytes(32)),
            'account' => $this->_account,
        ];
        $sign_data = json_decode(json_encode($data), true);
        $sign_data['key1'] = $this->_key_1;
        $sign_data['key2'] = $this->_key_2;
        $data['signature'] = strtoupper(hash_hmac('sha256', implode(':', $sign_data), $this->_key_1.$this->_key_2));
        return $this->query($method, 'post', $data);
    }

    private function query($method, $httpMethod, array $data = [])
    {
        $url = $method;
        if (!preg_match('/^http.*$/', $method)) {
            $url = $this->_url . $method;
        }

        $ch = curl_init();

        if ( strtolower($httpMethod) === 'post') {

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
            }
        }

        Log::channel('pay')->info('Inwizo request: '.$url);
        Log::channel('pay')->info(var_export($data, true));

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        $text = curl_exec($ch);
        $err = curl_error($ch);
        $resp_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $resp = json_decode($text, true);

        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);

        Log::channel('pay')->info('curl verbose');
        Log::channel('pay')->info(htmlspecialchars($verboseLog));

        if ($err) {
            Log::channel('pay')->error($err);
        }

        Log::channel('pay')->info('Inwizo resp:');
        Log::channel('pay')->info('code:'.$resp_code);
        Log::channel('pay')->info($text);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__.' callback deposit request');
        Log::channel('pay')->info(var_export($request->all(), true));

        $data = $request->all();
        $opertype = $data["opertype"]?? '';

        if (!isset($data['number'])) {
            Log::channel('pay')->error('number field not set');
            return response('', 500);
        }

        Log::channel('pay')->info('Start processing Inwizo transaction with id ' . $data['number']);

        /** @var PayoutTransaction $transaction */
        $transaction = PayoutTransaction::where('id', $data['number'])
            ->whereIn('status_id', [
                PayoutTransaction::STATUS_INWIZO_PENDING,
                PayoutTransaction::STATUS_INWIZO_FAIL,
            ])
            ->first();

        if ($transaction->status_id === PayoutTransaction::STATUS_INWIZO_FAIL) Log::channel('pay')
            ->info(__CLASS__." transaction was in fail state");

        $api = new Inwizo();
        if (!$api->checkSign($data)) {
            $method = 'ext';
            if (mb_strtoupper($data['amountcurr']) === 'KZT') $method = 'ext-KZT';
            $api = new Inwizo($method, $transaction->coment);
            if (!$api->checkSign($data)) {
                Log::channel('pay')->error('check sign fail');
//              return response('wrong sign', 500);
            }
        }

        if ($transaction === null) {
            if (PayoutTransaction::where('id', $data['number'])
                    ->where('status_id', PayoutTransaction::STATUS_INWIZO_SUCCESS)
                    ->first() !== null) {
                Log::channel('pay')->error('Transaction with id ' . $data['number'] . ' is already complete found. Request: ' . PHP_EOL . var_export($request->all(), true));
                return response('OK', 200);
            }
            Log::channel('pay')->error('Transaction with id ' . $data['number'] . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
            return response('transaction not found', 404);
        }

        // Find user which create this transaction.
        /** @var MUser $user $user */
        $user = MUser::where('id', $transaction->user_id)->first();

        if ($user === null) {
            Log::channel('pay')->error('User of the transaction is not found.');
            return response('user not found', 500);
        }


        if ($opertype !== '') {
            Log::channel('pay')->error('opertype '.$opertype);
            $transaction->status_id = PayoutTransaction::STATUS_INWIZO_FAIL;
            $transaction->coment = $opertype;
            return 'OK';
        }

        if (!isset($data['finalamount'])) {
            Log::channel('pay')->info('Transaction with id ' . $data['number'] . ' found, final amount not sent');
            $amount = $data['payamount'];
        } else{
            $amount = $data['finalamount'];
        }


        $currency = UserWallet::getCurrencyCode(mb_strtoupper($data['amountcurr']));
        $transaction->fee = $amount * 0.055;
        if ($transaction->coment === 'trusted') $transaction->fee = $amount * 0.10;
        $transaction->card_number = $data['PAN']??'';

        Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_INWIZO_SUCCESS, $currency);

        return 'OK';
    }

    public static function confirmCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__.' callback deposit request');
        Log::channel('pay')->info(var_export($request->all(), true));

        $data = $request->all();
        $opertype = $data["opertype"]?? '';

        if (!isset($data['number'])) {
            Log::channel('pay')->error('number field not set');
            return response('', 500);
        }

        Log::channel('pay')->info('Start processing Inwizo transaction with id ' . $data['number']);

        /** @var PayoutTransaction $transaction */
        $transaction = PayoutTransaction::where('id', $data['number'])
            ->where('status_id', PayoutTransaction::STATUS_INWIZO_PENDING)
            ->first();

        $api = new Inwizo();
        if (!$api->checkSign($data)) {
            $method = 'ext';
            if (mb_strtoupper($data['amountcurr']) === 'KZT') $method = 'ext-KZT';
            $api = new Inwizo($method, $transaction->coment);
            if (!$api->checkSign($data)) {
                Log::channel('pay')->error('check sign fail');
//              return response('wrong sign', 500);
            }
        }

        if ($transaction === null) {
            if (PayoutTransaction::where('id', $data['number'])
                    ->where('status_id', PayoutTransaction::STATUS_INWIZO_SUCCESS)
                    ->first() !== null) {
                Log::channel('pay')->error('Transaction with id ' . $data['number'] . ' is already complete found. Request: ' . PHP_EOL . var_export($request->all(), true));
                return response('OK', 200);
            }
            Log::channel('pay')->error('Transaction with id ' . $data['number'] . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
            return response('transaction not found', 404);
        }

        // Find user which create this transaction.
        /** @var MUser $user $user */
        $user = MUser::where('id', $transaction->user_id)->first();

        if ($user === null) {
            Log::channel('pay')->error('User of the transaction is not found.');
            return response('user not found', 500);
        }

        if ($opertype !== 'pay' && $opertype !== '') {
            Log::channel('pay')->error('opertype '.$opertype);
            $transaction->status_id = PayoutTransaction::STATUS_INWIZO_FAIL;
            $transaction->coment = $opertype;
            return 'OK';
        }

        return $data['transID'];
    }

}
