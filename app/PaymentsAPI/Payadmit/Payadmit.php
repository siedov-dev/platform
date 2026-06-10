<?php

namespace App\PaymentsAPI\Payadmit;

use App\Helpers\Hosts;
use App\Models\PaymentMethodData;
use App\Models\PaymentSystems;
use App\Models\UserMoneyStat;
use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @docs https://mp.payadmit.com/ords/f?p=101:660:16539013623100:::::
 */
class Payadmit
{

    // Test
//    private string $url = "https://app-demo.payadmit.com";
//    private string $api_key = "Tpdh753g6xYBDbfPa1te0OFpOOZlx6sw";
//    private string $sign_key = "Iu1FpfYNjmZj";
    private int $shop_id = 6648;
    // Production
    private string $url = "https://app.payadmit.com";
    private string $api_key = "";
    private string $sign_key = "";

    public function __construct(?int $method_id = null)
    {
        if ($method_id !== null) {
            $keys_data = PaymentMethodData::getMethodData($method_id);
            if (!empty($keys_data)) {
                $this->api_key = $keys_data['api_key'];
                $this->sign_key = $keys_data['sign_key'];
            }
        }
    }

    public function balance()
    {
        $enpoint = "/api/v1/terminals/getBalance/" . $this->shop_id;
        $data = [];

        return $this->query($enpoint, 'get', $data);
    }

    public function paymentInfo(string $id)
    {
        $enpoint = "/api/v1/payments/" . $id;
        $data = [];

        return $this->query($enpoint, 'get', $data);
    }

    public function getPaymentMethod(string $type, string $currency = 'UAH')
    {
        $enpoint = "/api/v1/payment-methods/available";
        $data = [
            "paymentType" => $type,
            "currency" => $currency,
        ];
        return $this->query($enpoint, 'post', $data);
    }

    public function deposit(string $tr_id, float $amount, array $requisites, string $currency, string $method = "BAZPAYUAH", $countryCode = "KZ")
    {
        $enpoint = "/api/v1/payments";
        $data = [
            'referenceId' => $tr_id,
            'paymentType' => "DEPOSIT",
            'paymentMethod' => $method,
            'amount' => $amount,
            'currency' => $currency,
            'customer' => [
//                'firstName' => $requisites['first_name'],
//                'lastName' => $requisites['last_name'],
                'email' => $requisites['email'],
                'phone' => $requisites['phone'],
            ],
            "returnUrl" => route('pay.close-tab'),
            "successReturnUrl" => route('pay.success'),
            "declineReturnUrl" => route('pay.fail'),
            "webhookUrl" => Hosts::getFinHost($currency) . "/api/pay/payadmit",
        ];

        if (!empty($countryCode)) {
            $data['billingAddress']['countryCode'] = $countryCode;
        }

        return $this->query($enpoint, 'post', $data);
    }

    public function payout(string $tr_id, float $amount, array $requisites, string $currency = 'UAH', string $method = "BASIC_CARD", $countryCode = 'KZ')
    {
        $phone = $requisites['phone'];
        $phone = preg_replace('#\D#', '', $phone);
        $phone = substr($phone, 0, 3) . ' ' . substr($phone, 3);
        $enpoint = "/api/v1/payments";
        $data = [
            'referenceId' => $tr_id,
            'paymentType' => "WITHDRAWAL",
            'paymentMethod' => $method,
            'amount' => $amount,
            'currency' => $currency,
            'description' => $tr_id,
            'card' => [
                'cardNumber' => $requisites['cardNumber'],
                'cardholderName' => $requisites['card_holder'],
                'expiryMonth' => $requisites['month'],
                'expiryYear' => '20' . $requisites['year'],
            ],
            'customer' => [
//                'firstName' => $requisites['first_name'],
//                'lastName' => $requisites['last_name'],
                'email' => $requisites['email'],
                'phone' => $phone,
            ],
            "billingAddress" => [
                "countryCode" => $countryCode,
            ],
            "returnUrl" => route('landing'),
            "successReturnUrl" => route('pay.success'),
            "declineReturnUrl" => route('pay.fail'),
            "webhookUrl" => Hosts::getFinHost($currency) . "/api/pay/payadmit",
        ];

        return $this->query($enpoint, 'post', $data);
    }

    public function payoutMobile(string $tr_id, float $amount, array $requisites, string $currency, string $method, string $countryCode, MUser $user)
    {
        $phone = $requisites['phone'];
        $phone = preg_replace('#\D#', '', $phone);
        $phone = substr($phone, 0, 1) . ' ' . substr($phone, 1);
        $enpoint = "/api/v1/payments";
        $data = [
            'referenceId' => $tr_id,
            'paymentType' => "WITHDRAWAL",
            'paymentMethod' => $method,
            'amount' => $amount,
            'currency' => $currency,
            'description' => $tr_id,
            'customer' => [
//                'firstName' => $requisites['first_name'],
//                'lastName' => $requisites['last_name'],
                'email' => $requisites['email'],
                'phone' => $phone,
                'ip' => $user->last_ip,
                'locale' => $user->locale,
            ],
            "billingAddress" => [
                "countryCode" => $countryCode,
            ],
            "returnUrl" => route('landing'),
            "successReturnUrl" => route('pay.success'),
            "declineReturnUrl" => route('pay.fail'),
            "webhookUrl" => Hosts::getFinHost($currency) . "/api/pay/payadmit",
        ];

        return $this->query($enpoint, 'post', $data);
    }

    public function checkSign(string $body, string $sign): bool
    {
        Log::channel('pay')->info(__CLASS__ . ' $sign: ' . $sign);
        Log::channel('pay')->info(__CLASS__ . ' $body: ' . $body);
        Log::channel('pay')->info(__CLASS__ . ' hash_hmac: ' . hash_hmac('sha256', $body, $this->sign_key));

        return hash_hmac('sha256', $body, $this->sign_key) == $sign;
    }

    private function query($method, $httpMethod, array $data = [])
    {
        $post_data = json_encode($data);

        $url = $method;
        if (!preg_match('/^http.*$/', $method)) {
            $url = $this->url . $method;
        }

        $ch = curl_init();

        if (strtolower($httpMethod) === 'post') {

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->api_key,
            ]);

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

        } else {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $this->api_key,
            ]);

            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
            }
        }

        Log::channel('pay')->info(__CLASS__ . ' request: ' . $url);
        Log::channel('pay')->info(var_export($data, true));

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');

        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 30);

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

        Log::channel('pay')->info(__CLASS__ . ' resp:');
        Log::channel('pay')->info('code:' . $resp_code);
        Log::channel('pay')->info($text);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . ' callback deposit request');
        Log::channel('pay')->info(var_export($request->header('Signature'), true));
        Log::channel('pay')->info(var_export($request->getContent(), true));
        Log::channel('pay')->info(var_export($request->all(), true));

        $data = $request->all();

        if ($data['state'] === 'PENDING') return 'ok';
        if ($data['state'] === 'CHECKOUT') return 'ok';

        if ($data['paymentType'] !== 'DEPOSIT') return 'ok';

        if (!isset($data['referenceId'])) {
            Log::channel('pay')->error('order_id field not set');
            return response('', 500);
        }

        Log::channel('pay')->info('Start processing Payadmit transaction with id ' . $data['referenceId']);

        try {
            DB::beginTransaction();
            /** @var PayoutTransaction $transaction */
            $transaction = PayoutTransaction::where('id', $data['referenceId'])
                ->where('status_id', PayoutTransaction::STATUS_PAYADMIT_PENDING)
                ->lockForUpdate()
                ->first();

            if ($transaction === null) {
                if (PayoutTransaction::where('id', $data['referenceId'])
                        ->where('status_id', PayoutTransaction::STATUS_PAYADMIT_SUCCESS)
                        ->first() !== null) {
                    Log::channel('pay')->error('Transaction with id ' . $data['referenceId'] . ' is already complete found. Request: ' . PHP_EOL . var_export($request->all(), true));
                    return response('OK', 200);
                }
                Log::channel('pay')->error('Transaction with id ' . $data['referenceId'] . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
                return response('transaction not found', 404);
            }
            /** @var PaymentSystems $pay_method */
            $pay_method = PaymentSystems::getPayinSystemForMethod($transaction->method, Wallet::getCurrencyCode($transaction->currency));
//            $pay_method = PaymentMethod::where('currency_id', Wallet::getCurrencyCode($transaction->currency))
//                ->where('method_alias', $transaction->method)
//                ->where('type', PaymentMethod::TYPE_PAYIN)
//                ->first();

            $api = new Payadmit($pay_method?->getId());

            if ($api->checkSign($request->getContent(), $request->header('Signature'))) {
                Log::channel('pay')->error('check sign fail');
            } else {
                Log::channel('pay')->error('check sign ok');
            }

            // Find user which create this transaction.
            /** @var MUser $user $user */
            $user = MUser::where('id', $transaction->user_id)->first();

            if ($user === null) {
                Log::channel('pay')->error('User of the transaction is not found.');
                return response('user not found', 500);
            }

            if ($data['state'] !== 'COMPLETED') {
                Log::channel('pay')->error('Transaction status ' . $data['state']);
                $transaction->status_id = PayoutTransaction::STATUS_PAYADMIT_FAIL;
                $transaction->coment = $data['state'];
                return 'ok';
            }

            $amount = $data['amount'];

            $currency = Wallet::getCurrencyCode($transaction->currency);
            $transaction->fee = $amount * 0.11 + 2;

            Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_PAYADMIT_SUCCESS, $currency);
            $full_method_name = explode('.', $transaction->method)[1];
            $method_prefix = explode(':', $full_method_name)[0]?? '';
            if (str_starts_with($method_prefix, "mobile")) {
                $stat = UserMoneyStat::getStat($user, $currency, UserMoneyStat::TYPE_PAYADMIT_MOBILE);
                $stat?->addDeposit($amount);
//                if ($stat === null) {
//                    $stat = new UserMoneyStat();
//                    $stat->setUserId($user->getId());
//                    $stat->setCurrencyId($currency);
//                    $stat->setType(UserMoneyStat::TYPE_PAYADMIT_MOBILE);
//                    $stat->setFirstDep(now());
//                }
//                $stat->setDepositCount($stat?->getDepositCount() + 1);
//                $stat->setDepositSum($stat?->getDepositSum() + $amount);
//                $stat->setLastDep(now());
//                $stat->save();
            } else {
                $stat = UserMoneyStat::getStat($user, $currency, UserMoneyStat::TYPE_PAYADMIT);
                $stat?->addDeposit($amount);

//                if ($stat === null) {
//                    $stat = new UserMoneyStat();
//                    $stat->setUserId($user->getId());
//                    $stat->setCurrencyId($currency);
//                    $stat->setType(UserMoneyStat::TYPE_PAYADMIT);
//                    $stat->setFirstDep(now());
//                }
//                $stat->setDepositCount($stat?->getDepositCount() + 1);
//                $stat->setDepositSum($stat?->getDepositSum() + $amount);
//                $stat->setLastDep(now());
//                $stat->save();
            }

            return 'OK';
        } catch (\Exception $e) {
            Log::channel('stack')->error($e->getMessage());
        } finally {
            DB::commit();
        }
    }
}
