<?php

namespace App\PaymentsAPI\SredaPay;

use App\Models\PaySystemsConfig;
use App\MUser;
use App\Payin\Payin;
use App\PaymentsAPI\Paze\PazeApi;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class SredaPay
{

    private string $url = "https://365.sredapay.kz/partner";
    private string $service_id;
    private string $secret_key;
    private string $secret_payout = "3FQYm2SNZgXCSkz5TbjNyu1QG";
    private string $secret_payout_eur = "cMWX8D55MFNzoNkpGKBrr4ZcFfExrQXG";
    private int $payout_service_id = 2826;

    const RESP_OK = 0;
    const RESP_RETRY = 1;
    const RESP_ERROR = 2;

    public function __construct(bool $payout = false, $method = '')
    {
        $conf = PaySystemsConfig::getSystemConfig(Payin::TYPE_SREDAPAY);
        $this->service_id = $conf['service_id']??'';
        $this->secret_key = $conf['secret_key']??'';
        if ($payout) $this->secret_key = $this->secret_payout;

        if ($method === '3-3000' || $method === '3-3001') {
            $this->secret_key = $this->secret_payout_eur;
        }
    }

    public function deposit($tr_id, $phone, $user_id, $amount, $otp_method)
    {
        $enpoint = "/$otp_method/pay";
        $params = [
            'orderid' => $tr_id,
            'goodphone' => 1,
            'ctn' => $phone,
            'smstext' => '452'.' '.$user_id.' '.$amount,
            'dt' => now()->timezone('Europe/Moscow')->format('YmdHis'),
        ];

        return $this->query($enpoint, 'post', $params);
    }

    public function confirmOtp($id, $otp, $otp_method)
    {
        $enpoint = "/$otp_method/pay_otp";
        $params = [
            'id' => $id,
            'otp' => $otp,
        ];

        return $this->query($enpoint, 'post', $params);
    }

    public function resendOtp($id, $otp_method)
    {
        $enpoint = "/$otp_method/resend_otp";
        $params = [
            'orderid' => $id,
        ];

        return $this->query($enpoint, 'post', $params);
    }

    public static function otpConfirm(Request $request)
    {
//        dd($request->all());
        $api = new SredaPay();
        $otp_method = $request->otpmethod;
        if ($request->action === 'resend') {
            $resp = $api->resendOtp($request->otpid, $otp_method);
            if ($resp !== false && intval($resp['result']) === 0) return view('pay.requisites.payin.sreda-otp-form',
                ['id' => $request->otpid, 'otp_method' => $otp_method, 'msg' => __('wallet.success')]);
            return view('pay.requisites.payin.sreda-otp-form',
                ['id' => $request->otpid, 'otp_method' => $otp_method, 'error' => $resp['descr']??'Error resending code']);
        }
        if ($request->action === 'confirm') {
            if (empty($request->otp)) {
                return view('pay.requisites.payin.sreda-otp-form',
                    ['id' => $request->otpid, 'otp_method' => $otp_method, 'error' => __('wallet.enter_otp_code')]);
            }
            $resp = $api->confirmOtp($request->otpid, $request->otp, $otp_method);
            if ($resp !== false && intval($resp['result']) === 0) return view('pay.requisites.payin.sreda-otp-form',
                ['id' => $request->otpid, 'otp_method' => $otp_method, 'msg' => 'Success!', 'success' => true]);
            return view('pay.requisites.payin.sreda-otp-form',
                ['id' => $request->otpid, 'otp_method' => $otp_method, 'error' => $resp['descr']??'unknown error']);
        }
        return view('pay.requisites.payin.sreda-otp-form', ['id' => $request->otpid, 'otp_method' => $otp_method, 'error' => 'Unknown action']);
    }

    public function balance()
    {
        $enpoint = '/balance';
        $params = [
            'dt' => now()->timezone('Europe/Moscow')->format('YmdHis'),
        ];
        $params['hash'] =  md5($params['dt'] . $this->secret_key);

        return $this->query($enpoint, 'post', $params, 'hash');

    }

    public function payout(string $tr_id, string $otp_method, string $dt, float $amount, $client_acc, $method = 10, $fio = '')
    {
        $this->url = "https://sredapay.kz/partner";
        $enpoint = "/{$this->payout_service_id}/payout";
        $params = [
            'id' => $tr_id,
            'dt' => $dt,
            'amount' => $amount,
            'phone' => str_replace('+7', '', $client_acc),
            'client' => str_replace('+7', '', $client_acc),
            'destination' => $method,
            'receiver_fio' => $fio,
            'currency' => 'KZT',
            'receiver_country' => 'KZ',
        ];

        $params['hash'] =  md5($tr_id . $dt . $amount . str_replace('+7', '', $client_acc) . str_replace('+7', '', $client_acc) . $method . $this->secret_payout);

        return $this->query($enpoint, 'post', $params, 'hash');
    }

    public function payoutEur(string $tr_id, string $dt, float $amount, $credentials, $currency = 'EUR', $method = 3, $service = 3000)
    {
        $this->url = "https://gwp.digital";
        $enpoint = "/$service/payout";
        $params = [
            'id' => $tr_id,
            'dt' => $dt,
            'amount' => $amount,
            'phone' => $credentials['phone'],
            'client' => $credentials['cardNumber'],
            'destination' => $method,
            'currency' => $currency,
            'receiver_fio' => $credentials['fio'],
            'receiver_country' => $credentials['receiver_country'],
            'receiver_city' => $credentials['receiver_city'],
            'receiver_birth_date' => $credentials['birthday'],
            'card_holder' => $credentials['month'].'/'.$credentials['year'],
            'card_expire' => $credentials['card_holder'],
        ];

        $params['hash'] =  md5($tr_id . $dt . $amount . $credentials['phone']. $credentials['cardNumber'] . $method . $this->secret_payout_eur);

        return $this->query($enpoint, 'post', $params, 'hash');
    }

    public function payoutInfo(string $id, string $otp_method, string $dt, float $amount, string $destination)
    {
        $this->url = "https://sredapay.kz/partner";
        $enpoint = "/{$this->payout_service_id}/check";
        $params = [
            'id' => $id,
            'dt' => $dt,
            'amount' => $amount,
            'destination' => $destination,
            'currency' => 'KZT',
            'service_id' => $this->service_id,
        ];
        $params['hash'] =  md5($id . $dt . $amount . $destination . $this->secret_payout);

        return $this->query($enpoint, 'post', $params, true);
    }

    public function payoutInfoEur(string $id, string $dt, float $amount, string $destination, $service = 3000)
    {
        $this->url = "https://gwp.digital";
        $enpoint = "/$service/check";
        $params = [
            'id' => $id,
            'dt' => $dt,
            'amount' => $amount,
            'destination' => $destination,
            'service_id' => $this->service_id,
        ];
        $params['hash'] =  md5($id . $dt . $amount . $destination . $this->secret_payout_eur);

        return $this->query($enpoint, 'post', $params, true);
    }

    private function sign(array $data): string
    {
        return md5(implode('', $data).$this->secret_key);
    }

    public static function payCallback(Request $request)
    {
        Log::channel('pay')->info('SredaPay callback request');
        Log::channel('pay')->info(var_export($request->all(), true));
        Log::channel('pay')->info(var_export($request->getContent(), true));
        $api = new SredaPay();

        $data = $request->all();
        if (empty($request->id))  {
            Log::channel('pay')->info('empty id field');
        }

        Log::channel('pay')->info('Start processing SredaPay transaction with id(coment) ' . $data['id']);

        // Find transaction.
        /** @var PayoutTransaction $transaction */
        $transaction = PayoutTransaction::where('coment', $data['id'])
            ->where('status_id', PayoutTransaction::STATUS_SREDAPAY_PENDING)
            ->first();

        if ($transaction === null) {
            $transaction = PayoutTransaction::where('coment', $data['id'])
                ->where('status_id', PayoutTransaction::STATUS_SREDAPAY_SUCCESS)
                ->first();
            if ($transaction !== null) {
                Log::channel('pay')->error('Transaction with id ' . $data['id'] . ' is already completed. Request: ' . PHP_EOL . var_export($request->all(), true));
                return response($api->responseText(self::RESP_OK, 'transaction already completed'), 200);
            }
            Log::channel('pay')->error('Transaction with id ' . $data['id'] . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
            return response($api->responseText(self::RESP_RETRY, 'transaction not found'), 404);
        }

        // Find user which create this transaction.
        /** @var MUser $user $user */
        $user = MUser::where('id', $transaction->user_id)->first();

        if ($user === null) {
            Log::channel('pay')->error('User of the transaction is not found.');
            return response($api->responseText(self::RESP_ERROR, 'user not found'), 404);
        }

        if ($data['result'] != 0) {
            $transaction->status_id = PayoutTransaction::STATUS_SREDAPAY_FAIL;
            $transaction->save();
            Log::channel('pay')->info('status failed');
            return response($api->responseText(self::RESP_OK, 'ok'), 200);
        }

        $amount = $data['amount']?? $transaction->sum;
        $transaction->fee = $amount * 0.12;

        $currency = UserWallet::getCurrencyCode($data['currency']?? $transaction->currency);

        if (!empty($data['paymentMethodDetails']['customerAccountNumber'])) {
            $transaction->card_number = $data['paymentMethodDetails']['customerAccountNumber'];
        }

        Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_SREDAPAY_SUCCESS, $currency);
        return response($api->responseText(self::RESP_OK, 'ok'), 200);
    }

    public function responseText($code, $descr): string
    {
        $text = '<?xml version="1.0" encoding="UTF-8"?>
            <response>
            <result>'.$code.'</result>
            <descr>'.$descr.'</descr>
            </response>';
        return $text;
    }

    private function query($method, $httpMethod, array $data = [], $payout = false)
    {
        $url = $this->url . $method;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
        ]);

        $sign = $this->sign($data);
        if (!$payout) $data['control'] = $sign;

        if ( strtolower($httpMethod) === 'post') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            if (!empty($data)) {
                $url .= '&' . http_build_query($data);
            }
        }

        Log::channel('pay')->info('SredaPay request: '.$url);
        Log::channel('pay')->info(var_export($data, true));

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,  true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        $text = curl_exec($ch);
        $err = curl_error($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        @$xml = simplexml_load_string($text, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $resp = json_decode($json, true);

        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);

        Log::channel('pay')->info('curl verbose');
        Log::channel('pay')->info(htmlspecialchars($verboseLog));

        if ($err) {
            Log::channel('pay')->error($err);
        }

        Log::channel('pay')->info('SredaPay resp:');
        Log::channel('pay')->info('code:'.$httpcode);
        Log::channel('pay')->info($text);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }

    public static function payoutCallback(Request $request)
    {
        Log::channel('pay')->info('SredaPay payout callback');
        Log::channel('pay')->info(var_export($request->all(), true));
        Log::channel('pay')->info(var_export($request->getContent(), true));
    }
}
