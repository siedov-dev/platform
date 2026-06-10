<?php

namespace App\PaymentsAPI\Aifory;

use App\Helpers\Hlog;
use App\Helpers\Hosts;
use App\Models\UserMoneyStat;
use App\MUser;
use App\Payin\Payin;
use App\Payout\Payout;
use App\PayoutTransaction;
use App\Services\KPay\KPay;
use App\UserWallet;
use App\ApplicationForPayment;
use App\Wallet;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use \Carbon\Carbon;

/**
 * @docs https://docs.euphoria.inc/#/
 */
class Aifory
{

    private string $api_key = "eMGs7oyofLaLZ1pc4CnfqJVVQlGSD0Jfi1V23SkwNTcgZIGVfkFsdChFpuH8VrBMDf6iDnomFNHwodLjaJQVLjMxMmODqbpY9BwnDmI2j5zIKX1qHBJCFePKboYY9v7hXrVKYOeqrIl6C5NZk46VvmMIgyH9Uar6TcTANmUArQwENarMfUll9Eizh3XF041vqYLSAgun3ciWrEv9iGICqeRLckutGmwyR2U9atHqEoTiTQiBHfVasDHNLLqw49mZ";
    private string $secret_key = "0vZHum60YjoxBsB77yTgPDshKdwLpKIJbuJEKpAUOC2sncGoo7xxIMwrZXijF2imFyg1JnqJfgadTsuc9zMhgsBDbxPhX5fnzyJHKcwNdtEtxHuqxF1pdPnZ2OPvWBOQT8miY7KfJUUC8jjEjCi2PCdw4wkzw9Zt1V5M0iMrtYyfk8R1LQYrnORX4bHsKHrQaAKhDnJnivGlMOjxsqOn3kkTNLiHJWrnXaFPZWGxqai3w8NRHjMKXRAqJJhE2tjH";
    private string $api_url = "https://api.euphoria.inc";

    public function getBalance()
    {
        $method = "/account/balance";
        $data = [];

        return $this->query($method, 'get', $data);
    }

    public function currenciesList()
    {
        $method = "/info/currency";
        $data = [];

        return $this->query($method, 'get', $data);
    }

    public function invoice(string $tr_id, float $amount, int $currency_id)
    {

        $method = "/payin/process";
        $data = [
            'amount' => $amount,
            'clientOrderID' => $tr_id,
            'currencyID' => $this->getCurrencyId($currency_id),
            'typeID' => 4,
            'TTL' => 999,
            'webhookURL' => Hosts::getFinHost(UserWallet::currencyName($currency_id)) . '/api/pay/aifory/deposit',
            'extra' => [
                'comment' => 'Kraken casino deposit',
                'successRedirectURL' => route('pay.success', [], true),
                'failedRedirectURL' => route('pay.fail', [], true),
                'allowedMethodIDs' => [3, 61, 4, 1, 10, 6, 12, 8, 9, 62],
                "payerInfo" => [
                    "userAgent" => $_SERVER['HTTP_USER_AGENT'],
                    "IP" => \request()->ip(),
                    "userID" => (string)Auth::id(),
                    "fingerprint" => hash('sha256', Auth::id() . \request()->ip() . $_SERVER['HTTP_USER_AGENT']),
                    "registeredAt" => Carbon::parse(Auth::user()->created_at)->timestamp
                ]
            ]

        ];


        $resp = $this->query($method, 'post', $data);
        return $resp;
    }

    public function invoiceUntrusted(string $tr_id, float $amount, int $currency_id)
    {

        $this->api_key = "Ga95P5oJNKfHv56oAEBesxqt51sDoiH3YLYMm3xqmipfbBSZ3KeB3Hbw5BGquPsFyCaiueI8CGFoGcLVpkBQCKHdmIAQGG5WGWLcdqvrRx9gsumacLfkoyj3Q2pj1VBUtzuRm36QzFR0mR4LXobwxnHbC9GkpAPA0Wpg3jDoBifOSCjkVuWNTioOv1Ny26PFsdwLvYjAJGyvGDDaoxjQhMW7Phf2VOcHa88Wf6md7sT5LkKiZaTwmV68In3yep9u";
        $this->secret_key = "9mkXbHdRhF7buNhONk7DHeyoXDKWxhnlg5asMO7D94NXRY7VVvECYnuwyyhfMfHmpIz2ZqnsaEJ5Lp72j2zQgFIQ4imkUMpg6bf12GyZztPWT7Ly4CXrajlDYnAQ6Sr2h5HyO1zmOBlp4Wv16e7qWGGcN1kjsVGApJmbHB6yjhyMwSuhj9CbuuTHiiXXDaN9m7Vus0Pf9z7sfvv1pRGyRWJjPflM4izui3XtirodQGRYGmwlXKlcCNmUwQEYJhvC";

        $method = "/payin/process";
        $data = [
            'amount' => $amount,
            'clientOrderID' => $tr_id,
            'currencyID' => $this->getCurrencyId($currency_id),
            'typeID' => 4,
            'TTL' => 999,
            'webhookURL' => Hosts::getFinHost(UserWallet::currencyName($currency_id)) . '/api/pay/aifory/deposit',
            'extra' => [
                'comment' => 'Kraken casino deposit',
                'successRedirectURL' => route('pay.success', [], true),
                'failedRedirectURL' => route('pay.fail', [], true),
                'allowedMethodIDs' => [508, 509],
                "payerInfo" => [
                    "userAgent" => $_SERVER['HTTP_USER_AGENT'],
                    "IP" => \request()->ip(),
                    "userID" => (string)Auth::id(),
                    "fingerprint" => hash('sha256', Auth::id() . \request()->ip() . $_SERVER['HTTP_USER_AGENT']),
                    "registeredAt" => Carbon::parse(Auth::user()->created_at)->timestamp,
                ],
            ],
        ];


        $resp = $this->query($method, 'post', $data);
        return $resp;
    }

    public function p2p(string $tr_id, float $amount, int $currency_id)
    {
        $method = "/payin/process";
        $data = [
            'amount' => $amount,
            'clientOrderID' => $tr_id,
            'currencyID' => $this->getCurrencyId($currency_id),
            'typeID' => 4,
            'TTL' => 5000,
            'webhookURL' => Hosts::getFinHost(UserWallet::currencyName($currency_id)) . '/api/pay/aifory/deposit',
            'extra' => [
                'comment' => 'Kraken casino deposit',
                'successRedirectURL' => route('pay.success', [], true),
                'failedRedirectURL' => route('pay.fail', [], true),
                'allowedMethodIDs' => [14, 1, 10],
                "payerInfo" => [
                    "userAgent" => $_SERVER['HTTP_USER_AGENT'],
                    "IP" => \request()->ip(),
                    "userID" => (string)Auth::id(),
                    "fingerprint" => hash('sha256', Auth::id() . \request()->ip() . $_SERVER['HTTP_USER_AGENT']),
                    "registeredAt" => Carbon::parse(Auth::user()->created_at)->timestamp
                ]
            ]
        ];

        $resp = $this->query($method, 'post', $data);
        return $resp;
    }

    public function invoiceByMethodIds(string $tr_id, float $amount, int $currency_id, array $methods = [61,])
    {
        $method = "/payin/process";
        $data = [
            'amount' => $amount,
            'clientOrderID' => $tr_id,
            'currencyID' => $this->getCurrencyId($currency_id),
            'typeID' => 4,
            'TTL' => 5000,
            'webhookURL' => Hosts::getFinHost(UserWallet::currencyName($currency_id)) . '/api/pay/aifory/deposit',
            'extra' => [
                'comment' => 'Kraken casino deposit',
                'successRedirectURL' => route('pay.success', [], true),
                'failedRedirectURL' => route('pay.fail', [], true),
                'allowedMethodIDs' => $methods,
                "payerInfo" => [
                    "userAgent" => $_SERVER['HTTP_USER_AGENT'],
                    "IP" => \request()->ip(),
                    "userID" => (string)Auth::id(),
                    "fingerprint" => hash('sha256', Auth::id() . \request()->ip() . $_SERVER['HTTP_USER_AGENT']),
                    "registeredAt" => Carbon::parse(Auth::user()->created_at)->timestamp
                ]
            ]
        ];

        $resp = $this->query($method, 'post', $data);
        return $resp;
    }


    public function payout($tr_id, $amount, $currency, $wallet, $paySys)
    {
        $methodId = null;
        $recipientTypeID = 1;
        if ($paySys == 'aifory.rubsb') {
            $recipientTypeID = 4;
            $methodId = 61;
        } else if ($paySys == 'aifory.rubtinkoff') {
            $methodId = 62;
        }

        $method = "/payout/process";
        $data = [
            'currencyID' => $this->getCurrencyId(Wallet::getCurrencyCode($currency)),
            'amount' => intval($amount),
            'recipientTypeID' => $recipientTypeID,
            'recipient' => $wallet,
            'clientOrderID' => strval($tr_id),
            'webhookURL' => Hosts::getFinHost($currency) . '/api/pay/aifory/withdraw',
        ];
        if ($methodId !== null) {
            $data['extra']['methodID'] = $methodId;
        }
        $resp = $this->query($method, 'post', $data);
        Log::channel('payout')->info('Aifory payout result: ' . var_export($resp, true));
        return $resp;
    }

    public function getCurrencyId(int $currency_id): int
    {
        return match ($currency_id) {
            UserWallet::CURRENCY_RUB => 3,
            UserWallet::CURRENCY_UAH => 11,
            UserWallet::CURRENCY_KZT => 7,
            UserWallet::CURRENCY_USDT => 5,
            UserWallet::CURRENCY_UZS => 10,
        };
    }

    public function getRequestCurrencyId(int $currency_id): int
    {
        return match ($currency_id) {
            3 => UserWallet::CURRENCY_RUB,
            11 => UserWallet::CURRENCY_UAH,
            7 => UserWallet::CURRENCY_KZT,
            5 => UserWallet::CURRENCY_USDT,
            10 => UserWallet::CURRENCY_UZS,
        };
    }

    public function getCurrencyBanks(int $currency_id)
    {
        return match ($currency_id) {
            UserWallet::CURRENCY_RUB => [1, 2, 3, 4, 5, 6, 7, 8, 9,],
            UserWallet::CURRENCY_UAH => [],
            UserWallet::CURRENCY_KZT => [301, 302, 303, 304, 305, 306, 307,],
        };
    }

    public function listP2pMethods()
    {
        $method = "/info/p2p_method";
        $resp = $this->query($method, 'get');
        return $resp;
    }

    public function sign(string $data)
    {
        return hash_hmac('sha512', $data, $this->secret_key);
    }

    private function query($method, $httpMethod, array $data = [], string $user_agent = "ItG5)w")
    {
        $url = $this->api_url . $method;

        $ch = curl_init();

        $payload = json_encode($data);

        if ($httpMethod === 'get') {
            $sign = $this->sign("");
        } else {
            $sign = $this->sign($payload);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'API-Key: ' . $this->api_key,
            'Signature: ' . $sign,
            'Content-Type: application/json',
            'user-agent: ' . $user_agent
        ]);

        if (strtolower($httpMethod) === 'post') {

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
            }
        }

        Log::channel('pay')->info('Aifory request: ' . $url);
        Log::channel('pay')->info(var_export($data, true));
        Log::channel('pay')->info('Aifory request body: ' . $url);
        Log::channel('pay')->info($payload);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        $text = curl_exec($ch);
        $err = curl_error($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $resp = json_decode($text, true);

        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);

        Log::channel('pay')->info('curl verbose');
        Log::channel('pay')->info(htmlspecialchars($verboseLog));

        if ($err) {
            Log::channel('pay')->error($err);
        }

        Log::channel('pay')->info('Aifory resp:');
        Log::channel('pay')->info('code:' . $httpcode);
        Log::channel('pay')->info($text);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }

    public static function withdrawCallback(Request $request)
    {

        Log::channel('payout')->info(__CLASS__ . ' callback payout request');
        Log::channel('payout')->info(var_export($request->all(), true));

        $data = $request->all();

        if (!isset($data['clientOrderID'])) {
            Log::channel('payout')->error('clientOrderID field not set');
            return response('', 500);
        }

        /** @var ApplicationForPayment $applicationForPayment */
        $applicationForPayment = ApplicationForPayment::where('id', intval($data['clientOrderID']))
            ->where('confirmed', ApplicationForPayment::STATUS_AUTO_PAY_PROCESS)
            ->first();

        if (!$applicationForPayment) {
            Log::channel('payout')->error('Transaction with id ' . $data['clientOrderID'] . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
            return response('transaction not found', 404);
        }

        if ($data['statusID'] == 3) {
            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
            $applicationForPayment->save();
            Log::channel('payout')->error('Transaction ' . $data['clientOrderID'] . ' status error');
            return response('OK', 200);
        }

        if ($data['statusID'] == 2) {
            $applicationForPayment->outer_id = $data['ID'];
            $applicationForPayment->comentar = 'Заявка завершена';
            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
            $applicationForPayment->save();

            Log::channel('payout')->error('Transaction ' . $data['clientOrderID'] . ' status success');

            DB::table('payout_transactions')
                ->insert([
                    'user_id' => $applicationForPayment->user_id,
                    'hall_id' => $applicationForPayment->hall_id,
                    'date' => date('Y-m-d H:i:s'),
                    'currency' => UserWallet::currencyName($applicationForPayment->currency),
                    'operation' => 'Автовыплата Aifory',
                    'sum' => $applicationForPayment->money,
                    'fee' => $data['fee'],
                    'total_sum' => $applicationForPayment->money + $data['fee'],
                    'status_id' => PayoutTransaction::PAID_OUT_VISA,
                    'pay_system_id' => Payout::TYPE_AIFORY,
                ]);

            self::updateStats(MUser::find($applicationForPayment->getUserId()), $applicationForPayment->getCurrency());

            return response('OK', 200);
        }

    }


    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . ' callback deposit request');
        Log::channel('pay')->info(var_export($request->all(), true));

        $data = $request->all();

        if (!isset($data['clientOrderID'])) {
            Log::channel('pay')->error('clientOrderID field not set');
            return response('', 500);
        }

        Log::channel('pay')->info('Start processing Sandbox transaction with id ' . $data['clientOrderID']);

        $api = new Aifory();
//        if (!$api->checkSign($request->getContent(), $request->header('X-Signature'))) {
//            Log::channel('pay')->error('check sign fail');
//            return response('wrong sign', 500);
//        }

        try {
            DB::beginTransaction();
            /** @var PayoutTransaction $transaction */
            $transaction = PayoutTransaction::where('id', $data['clientOrderID'])
                ->whereIn('status_id', [
                    PayoutTransaction::STATUS_AIFORY_PENDING,
                    PayoutTransaction::STATUS_AIFORY_FAIL,
                ])
                ->lockForUpdate()
                ->first();

            if ($transaction?->status_id === PayoutTransaction::STATUS_AIFORY_FAIL) Log::channel('pay')
                ->info(__CLASS__ . " transaction was in fail state");

            if ($transaction === null) {
                if (PayoutTransaction::where('id', $data['clientOrderID'])
                        ->where('status_id', PayoutTransaction::STATUS_AIFORY_SUCCESS)
                        ->first() !== null) {
                    Log::channel('pay')->error('Transaction with id ' . $data['clientOrderID'] . ' is already complete found. Request: ' . PHP_EOL . var_export($request->all(), true));
                    return response('ok', 200);
                }
                Log::channel('pay')->error('Transaction with id ' . $data['clientOrderID'] . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
                return response('transaction not found', 404);
            }

            // Find user which create this transaction.
            /** @var MUser $user $user */
            $user = MUser::where('id', $transaction->user_id)->first();

            if ($user === null) {
                Log::channel('pay')->error('User of the transaction is not found.');
                return response('user not found', 500);
            }

            if ($data['statusID'] == 3) {
                $transaction->status_id = PayoutTransaction::STATUS_AIFORY_FAIL;
                $transaction->save();
                return response('OK', 200);
            }

            if ($data['statusID'] != 2) {
                return response('OK', 200);
            }

            $amount = $data['successPaid'];

            $currency_id = $api->getRequestCurrencyId($data['currencyID']);
            $transaction->fee = $data['fee'];

            Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_AIFORY_SUCCESS, $currency_id);
        } catch (\Throwable $e) {
            Hlog::logException($e);
        } finally {
            DB::commit();
        }

        self::updateStats($user, $currency_id);
        return 'OK';
    }

    private static function updateStats(MUser $user, int $currency_id)
    {

        try {
            //update cache stats
            $stat = UserMoneyStat::getStat($user, $currency_id, UserMoneyStat::TYPE_AIFORY, true);
//            if ($stat === null) {
//                $stat = new UserMoneyStat();
//                $stat->setUserId($user->getId());
//                $stat->setCurrencyId($currency_id);
//                $stat->setType(UserMoneyStat::TYPE_AIFORY);
//            }
//
//            $deps = PayoutTransaction::selectRaw('user_id, sum(`sum`) as sum, count(id) as cnt, max(`date`) as last_dep, min(`date`) as first_dep')
//                ->where('user_id', $user->getId())
//                ->whereIn('status_id', PayoutTransaction::getIncomingStatuses())
//                ->where('currency', UserWallet::currencyName($currency_id))
//                ->where('status_id', PayoutTransaction::STATUS_AIFORY_SUCCESS)
//                ->groupBy('user_id')
//                ->first();
//
//            $stat->setDepositCount($deps?->cnt ?? 0);
//            $stat->setDepositSum($deps?->sum ?? 0);
//            $stat->first_dep = $deps?->first_dep;
//            $stat->last_dep = $deps?->last_dep;
//
//            $payouts = PayoutTransaction::selectRaw('user_id, sum(`sum`) as sum, count(id) as cnt, max(`date`) as last_pay, min(`date`) as first_pay')
//                ->where('user_id', $user->getId())
//                ->whereIn('status_id', PayoutTransaction::getOutgoingStatuses())
//                ->where('currency', UserWallet::currencyName($currency_id))
//                ->where('operation', 'Автовыплата Aifory')
//                ->groupBy('user_id')
//                ->first();
//
//            $stat->setPayoutCount($payouts?->cnt ?? 0);
//            $stat->setPayoutSum($payouts?->sum ?? 0);
//            $stat->first_payout = $payouts?->first_pay;
//            $stat->last_payout = $payouts?->last_pay;
//            $stat->save();
        } catch (\Exception $e) {
            Hlog::logException($e, 'pay');
        }
    }
}
