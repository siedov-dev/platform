<?php

namespace App\PaymentsAPI\NashObmen;

use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NashObmen
{
    private int $client_id_uah = 140319;
    private int $bot_id_uah = 92;
    private int $client_id_rub = 361042;
    private int $bot_id_rub = 1474; 
    private string $_endpoint = "https://scmapi.top/api";
    private string $token_uah = "67DED22218B9E715A08A7B0DB2ADC034EC67A52A959825FD4EF2AC0C56D6D77A";
    private string $token_rub = "E69D97CBBD05F600131CDB8563E3D85D675322E74A31BD28FB1E5A6F6C79EDF2";

    public function getCard(float $amount, string $currency)
    {
        $clientId = $this->client_id_uah;
        $prefix = '';
        if ($currency === 'RUB') {
            $clientId = $this->client_id_rub;
            $prefix = '/auto';
        }

        $method = $prefix."/get_card/client/$clientId/amount/$amount/currency/$currency";
        if ($currency === 'RUB') $method .= '/niche/auto';


        return $this->query($method, 'get', []);
    }

    public function payout($tr_id, $amount, $currency, $wallet, $paySys)
    {
        Log::channel('payout')->info('NashObmen payout currency: ' . $currency);
        $method = '/create_payout';

        $data = [
            'asset' => 'USDT',
            'fiat' => $currency,
            'data' => [
                ['number' => $wallet, 'amount' => $amount]
            ]
        ];

        $data['token'] = $this->token_uah;
        if($currency == 'RUB') $data['token'] = $this->token_rub;

        $resp = $this->query($method, 'post', $data);
        Log::channel('payout')->info('NashObmen payout result: ' . var_export($resp, true));
        return $resp;
    }

    public function status(string $id)
    {
        $method = "/check_trade/trade/$id";
        $data = [];

        return $this->query($method, 'get', $data);
    }

    private function query($method, $httpMethod, array $data = [])
    {
        $url = $method;
        if (!preg_match('/^http.*$/', $method)) {
            $url = $this->_endpoint . $method;
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

        Log::channel('pay')->info(__CLASS__.' request: '.$url);
        Log::channel('pay')->info(var_export($data, true));

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
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

        Log::channel('pay')->info(__CLASS__.' resp:');
        Log::channel('pay')->info('code:'.$resp_code);
        Log::channel('pay')->info($text);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__.' callback deposit request');
        Log::channel('pay')->info(var_export($request->getContent(), true));

        $data = json_decode($request->getContent(), true);
        Log::channel('pay')->info(__CLASS__.' callback deposit decoded');
        Log::channel('pay')->info($data);

        if (!isset($data['trade'])) {
            Log::channel('pay')->error('number field not set');
            return response('', 500);
        }

        Log::channel('pay')->info('Start processing transaction with id ' . $data['trade']);

        /** @var PayoutTransaction $transaction */
        $transaction = PayoutTransaction::where('coment', $data['trade'])
            ->where('status_id', PayoutTransaction::STATUS_NASHOBMEN_PENDING)
            ->first();

        if ($transaction === null) {
            if (PayoutTransaction::where('coment', $data['trade'])
                    ->where('status_id', PayoutTransaction::STATUS_NASHOBMEN_SUCCESS)
                    ->first() !== null) {
                Log::channel('pay')->error('Transaction with id ' . $data['trade'] . ' is already complete found. Request: ' . PHP_EOL . var_export($request->all(), true));
                return response('ok', 200);
            }
            Log::channel('pay')->error('Transaction with id ' . $data['trade'] . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
            return response('transaction not found', 404);
        }

        // Find user which create this transaction.
        /** @var MUser $user $user */
        $user = MUser::where('id', $transaction->user_id)->first();

        if ($user === null) {
            Log::channel('pay')->error('User of the transaction is not found.');
            return response('user not found', 500);
        }

        $amount = $data['amount'];

        $currency = UserWallet::getCurrencyCode(mb_strtoupper($data['fiat']));
        $transaction->fee = $amount * 0.1;
        $transaction->card_number = $data['card_number']??'';

        Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_NASHOBMEN_SUCCESS, $currency);

        return 'OK';
    }

    public static function checkPayment(Request $request)
    {
        $trade = $request->input('trade');
        $api = new NashObmen();
        $data = $api->status($trade);
        if (empty($data[0]['result'])) {
            return response('System error!', 400);
        }
        if ($data[0]['result'] === 'success') {
            return response(__('cabinet.success'), 200);
        }
        if ($data[0]['message'] === 'still processing') {
            return response(__('cabinet.processing'), 400);
        }
        return response( __('cabinet.error'). ' ' . $data[0]['message'], 400);
    }

}
