<?php

namespace App\PaymentsAPI\HighHelp;

use App\Helpers\Hlog;
use App\Helpers\Hosts;
use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * @docs https://docs.highhelp.io/ecom/h2c_ecom/
 * @docs https://awesomedoc.highhelp.io/ru/HEAD/api/authentication.html
 */
class HighHelp
{
    private string $project_id = "";

    public function __construct(?string $project_id = "")
    {
        $this->project_id = $project_id?? "";
    }

    private string $endpoint_url = "https://api.hh-processing.com";

    public function balance()
    {
        $url = $this->endpoint_url . "/api/v1/balance/info";
        $payload = [
            "general" => [
                'project_id' => $this->project_id,
            ],
        ];
        $response = $this->makeRequest($this->project_id, $url, $payload);
        return json_decode($response, true);
    }

    public function getBanks()
    {
        $url = $this->endpoint_url . "/api/v1/bank/all";
        $payload = [
            "general" => [
                'project_id' => $this->project_id,
            ],
        ];
        $response = $this->makeRequest($this->project_id, $url, $payload);
        return json_decode($response, true);
    }

    public function depositEcom(float $amount, string $tr_id, string $method, MUser $user, string $currency = 'RUB')
    {
        $url = $this->endpoint_url . "/api/v1/widget/payin";
        $payload = [
            "general" => [
                "project_id" => $this->project_id,
                "payment_id" => $tr_id,
//                "merchant_callback_url" => "",
                "merchant_success_callback_url" => Hosts::getFinHost()."/api/pay/hh/deposit",
                "merchant_decline_callback_url" => Hosts::getFinHost()."/api/pay/hh/deposit",
            ],
            "payment" => [
                "widget_method" => $method,
                "amount" => intval($amount * 100), // Указывается в минорных единицах 111100 = 1111.00 рублей
                "currency" => $currency,
            ],
            "customer" => [
                "id" => $user->getId(),
                "ip_address" => $user->last_ip,
                "country" => "RU",
                "email" => $user->email,
            ]
        ];
        $response = $this->makeRequest($this->project_id, $url, $payload);
        return json_decode($response, true);
    }

    public function depositP2p(float $amount, string $tr_id, string $method, MUser $user, string $currency = 'RUB', string $traffic = "ftd", string $bank = "")
    {
        $url = $this->endpoint_url . "/api/v1/widget/payin";
        $payload = [
            "general" => [
                "project_id" => $this->project_id,
                "payment_id" => $tr_id,
//                "merchant_callback_url" => "",
                "merchant_success_callback_url" => Hosts::getFinHost()."/api/pay/hh/deposit",
                "merchant_decline_callback_url" => Hosts::getFinHost()."/api/pay/hh/deposit",
            ],
            "payment" => [
                "widget_method" => $method,
                "amount" => intval($amount * 100), // Указывается в минорных единицах 111100 = 1111.00 рублей
                "currency" => $currency,
            ],
            "sender" => [],
            "customer" => [
                "id" => $user->getId(),
                "ip_address" => $user->last_ip,
                "country" => "RU",
                "customer_type" => $traffic,
                "email" => $user->email,
            ]
        ];
        if ($bank !== "") {
            $payload['sender'] = [
                'bank_symbol' => $bank,
            ];
        }
        $response = $this->makeRequest($this->project_id, $url, $payload);
        return json_decode($response, true);
    }

    public function payoutEcom(float $amount, string $tr_id, string $method, MUser $user, array $requisites, string $currency = 'RUB')
    {
        $url = $this->endpoint_url . "/api/v1/payment/ecom/payout";
        $payload = [
            "general" => [
                "project_id" => $this->project_id,
                "payment_id" => $tr_id,
//                "merchant_callback_url" => "",
//                "merchant_success_callback_url" => Hosts::getFinHost()."/api/pay/hh/deposit",
//                "merchant_decline_callback_url" => Hosts::getFinHost()."/api/pay/hh/deposit",
            ],
            "receiver" => [
                "pan" => $requisites['cardNumber'],
                "year" => $requisites['year'],
                "month" => $requisites['month'],
            ],
            "payment" => [
                "method" => $method,
                "amount" => intval($amount * 100), // Указывается в минорных единицах 111100 = 1111.00 рублей
                "currency" => $currency,
                "description" => "payout",
            ],
            "customer" => [
                "id" => $user->getId(),
                "ip_address" => $user->last_ip,
                "country" => "RU",
                "email" => $user->email,
            ]
        ];
        $response = $this->makeRequest($this->project_id, $url, $payload);
        return json_decode($response, true);
    }

    public function payoutP2p(float $amount, string $tr_id, string $method, MUser $user, array $requisites, string $currency = 'RUB')
    {
        $url = $this->endpoint_url . "/api/v1/payment/p2p/payout";
        $payload = [
            "general" => [
                "project_id" => $this->project_id,
                "payment_id" => $tr_id,
//                "merchant_callback_url" => "",
//                "merchant_success_callback_url" => Hosts::getFinHost()."/api/pay/hh/deposit",
//                "merchant_decline_callback_url" => Hosts::getFinHost()."/api/pay/hh/deposit",
            ],
            "receiver" => $requisites['receiver'],
            "payment" => [
                "method" => $method,
                "amount" => intval($amount * 100), // Указывается в минорных единицах 111100 = 1111.00 рублей
                "currency" => $currency,
                "description" => "payout",
            ],
            "customer" => [
                "id" => $user->getId(),
                "ip_address" => $user->last_ip,
                "country" => "RU",
                "email" => $user->email,
            ]
        ];
        $response = $this->makeRequest($this->project_id, $url, $payload);
        return json_decode($response, true);
    }

    public function payoutInfoEcom(string $tr_id)
    {
        $url = $this->endpoint_url . "/api/v1/payment/ecom/payout/info";
        $payload = [
            "general" => [
                "project_id" => $this->project_id,
                "payment_id" => $tr_id,
            ],
        ];
        $response = $this->makeRequest($this->project_id, $url, $payload);
        return json_decode($response, true);
    }

    public function payoutInfoP2p(string $tr_id)
    {
        $url = $this->endpoint_url . "/api/v1/payment/p2p/payout/info";
        $payload = [
            "general" => [
                "project_id" => $this->project_id,
                "payment_id" => $tr_id,
            ],
        ];
        $response = $this->makeRequest($this->project_id, $url, $payload);
        return json_decode($response, true);
    }

    private function makeRequest(string $sub_merchant_id, string $url, array $payload): string
    {
        $private_key_pem = file_get_contents(__DIR__ . '/'.$this->project_id.'_key.pem');
        $private_key_openssl = openssl_pkey_get_private($private_key_pem);
        $details = openssl_pkey_get_details($private_key_openssl);
        $public_key_string = $details['key'];


        $public_key_string = str_replace(["\r\n", "\r"], "\n", $public_key_string);

        $api_key = base64_encode($public_key_string);

        $timestamp = time();

        $dumped = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        Log::channel("pay")->info(__CLASS__."Url: " . $url);
        Log::channel("pay")->info(__CLASS__."Request: " . $dumped);

        $joined_result = $this->normalize_message($payload);
        Log::channel("pay")->info(__CLASS__."Normalized message: " . $joined_result);

        $base64_encoded_message = base64_encode($joined_result);
        $message = $base64_encoded_message . $timestamp;
        Log::channel("pay")->info(__CLASS__."Message: " . $message);

        $private_key_openssl = openssl_pkey_get_private(file_get_contents(__DIR__ . '/'.$this->project_id.'_key.pem'));
        openssl_sign($message, $signature, $private_key_openssl, OPENSSL_ALGO_SHA256);

        $base64_sign = str_replace(
            ['+', '/'],
            ['-', '_'],
            base64_encode($signature)
        );

        $headers = [
            'Content-Type: application/json',
            'X-Access-Token: ' . $api_key,
            'X-Access-Signature: ' . $base64_sign,
            'X-Access-Merchant-Id: ' . $sub_merchant_id,
            'X-Access-Timestamp: ' . $timestamp,
        ];

        Log::channel("pay")->info(__CLASS__."Headers: " . json_encode($headers));

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $dumped,
        ];

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        Log::channel("pay")->info(__CLASS__."Response Status Code: " . $status_code);
        Log::channel("pay")->info(__CLASS__."Response: " . $response);
        return $response;
    }

    private function normalize_message($payload): string
    {
        $result = [];
        $this->parse_json('', $payload, $result);
        sort($result);
        return implode(';', $result);
    }

    private function parse_json($prefix, $obj, &$result)
    {
        if (is_array($obj)) {
            foreach ($obj as $key => $value) {
                $new_prefix = $prefix ? "{$prefix}:{$key}" : $key;
                $this->parse_json($new_prefix, $value, $result);
            }
        } else {
            if ($obj) {
                $result[] = "{$prefix}:{$obj}";
            } else {
                $result[] = "{$prefix}:None";
            }
        }
    }

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . " callback:", $request->all());
        Log::channel('pay')->info(__CLASS__ . " callback raw:". var_export($request->getContent(), true));
        $data = $request->all();
        Log::channel('pay')->info(__CLASS__ . " callback data:", $data);

        $targetUrl = "http://127.0.0.1:23106/pay/highhelp/deposit";
        $response = Http::withHeaders($request->headers->all())
            ->withBody($request->getContent(), $request->header('Content-Type'))
            ->send($request->method(), $targetUrl);
        Log::channel('pay')->info('highhelp callback response ' . var_export($response->body(), true));
        return response($response->body(), $response->status())->withHeaders($response->headers());
//
//        if (!isset($data['general']['payment_id'])) {
//            Log::channel('pay')->error('payment_id field not set');
//            return response('order_id not found', 404);
//        }
//
//        $order_id = $data['general']['payment_id'];
//
//        Log::channel('pay')->info('Start processing '.__CLASS__.' transaction with id ' . $order_id);
//
//        try {
//            DB::beginTransaction();
//
//            /** @var PayoutTransaction $transaction */
//            $transaction = PayoutTransaction::where('id', $order_id)
//                ->whereIn('status_id', [
//                    PayoutTransaction::STATUS_HIGHHELP_PENDING,
//                    PayoutTransaction::STATUS_HIGHHELP_FAIL,
//                ])
//                ->lockForUpdate()
//                ->first();
//
//            if ($transaction?->status_id === PayoutTransaction::STATUS_HIGHHELP_FAIL) {
//                Log::channel('pay')->warning('Transaction with id ' . $order_id . ' already failed found. Processing');
//            }
//
//            if ($transaction === null) {
//                if (PayoutTransaction::where('id', $order_id)
//                        ->where('status_id', PayoutTransaction::STATUS_HIGHHELP_SUCCESS)
//                        ->first() !== null) {
//                    Log::channel('pay')->error('Transaction with id ' . $order_id . ' is already complete found. Request: ' . PHP_EOL . var_export($request->all(), true));
//                    return response('ok', 200);
//                }
//                Log::channel('pay')->error('Transaction with id ' . $order_id . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
//                return response('transaction not found', 404);
//            }
//
//            // Find the user which created this transaction.
//            /** @var MUser $user $user */
//            $user = MUser::find($transaction->user_id);
//
//            if ($user === null) {
//                Log::channel('pay')->error('User of the transaction is not found.');
//                return response('user not found', 500);
//            }
//
//            if ($data['status']['status'] === 'decline') {
//                Log::channel('pay')->error('status:' . $data['status']['status_description']?? 'error');
//                $transaction->status_id = PayoutTransaction::STATUS_HIGHHELP_FAIL;
//                $transaction->coment = $data['status']['status_description']?? 'status error';
//                $transaction->save();
//                return 'ok';
//            }
//
//            if ($data['status']['status'] !== 'success') {
//                Log::channel('pay')->info('status:' . $data['status']['status']);
//                $transaction->coment = $data['status'] ?? 'pending status';
//                $transaction->save();
//                return 'ok';
//            }
//
//            $amount = $data['payment_info']['amount'] / 100;
//
//            $currency = Wallet::getCurrencyCode($transaction->getCurrency());
//            if ($transaction->method === 'HighHelp.payin-ecom-card') {
//                $transaction->fee = $amount * 0.13;
//            }
//            else if ($transaction->method === 'HighHelp.payin-p2p-mobile:1-click') {
//                $transaction->fee = $amount * 0.16;
//                }
//            else if ($transaction->method === 'HighHelp.payin-p2p-mobile') {
//                    $transaction->fee = $amount * 0.18;
//            } else {
//                $transaction->fee = $amount * 0.11;
//            }
//            if ($transaction->currency === 'UZS') {
//                $transaction->fee = $amount * 0.04;
//            }
//            $transaction->card_number = $data['card']['pan'] ?? '';
//
//
//            Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_HIGHHELP_SUCCESS, $currency);
//
//        } catch (\Throwable $e) {
//            Hlog::logException($e, 'pay');
//        } finally {
//            DB::commit();
//        }
//
//        return response('ok', 200);
    }
}
